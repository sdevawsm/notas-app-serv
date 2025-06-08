<?php

namespace LadyPHP\View;

class ElleCompiler
{
    private string $viewPath;
    private string $cachePath;
    private array $data = [];
    private array $sections = [];
    private ?string $currentLayout = null;

    public function __construct(string $viewPath, string $cachePath)
    {
        $this->viewPath = rtrim($viewPath, '/');
        $this->cachePath = rtrim($cachePath, '/');
        
        // Cria o diretório de cache se não existir
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    private function needsRecompilation(string $viewFile, string $cacheFile): bool
    {
        // Se o arquivo de cache não existe, precisa compilar
        if (!file_exists($cacheFile)) {
            return true;
        }

        // Obtém o timestamp da última modificação do arquivo de cache
        $cacheTime = filemtime($cacheFile);
        
        // Verifica se a view principal foi modificada
        if (filemtime($viewFile) > $cacheTime) {
            return true;
        }

        // Verifica se há includes e se eles foram modificados
        $content = file_get_contents($viewFile);
        if (preg_match_all('/@include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $matches)) {
            foreach ($matches[1] as $include) {
                $includeFile = $this->viewPath . '/' . $include . '.elle.php';
                if (file_exists($includeFile) && filemtime($includeFile) > $cacheTime) {
                    return true;
                }
            }
        }

        // Verifica se há @extends e se o layout foi modificado
        if (preg_match('/@extends\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $matches)) {
            $layoutFile = $this->viewPath . '/' . $matches[1] . '.elle.php';
            if (file_exists($layoutFile) && filemtime($layoutFile) > $cacheTime) {
                return true;
            }
        }

        return false;
    }

    public function compile(string $view, array $data = []): string
    {
        $this->data = $data;
        $this->sections = []; // Limpa as seções a cada nova compilação
        $this->currentLayout = null; // Limpa o layout atual
        
        $viewFile = $this->viewPath . '/' . $view . '.elle.php';
        $cacheFile = $this->cachePath . '/' . md5($view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("View {$view} não encontrada");
        }

        // Garante que o diretório de cache existe
        if (!is_dir($this->cachePath)) {
            if (!mkdir($this->cachePath, 0777, true)) {
                throw new \Exception("Não foi possível criar o diretório de cache: {$this->cachePath}");
            }
        }

        // Primeiro, lê o conteúdo da view para verificar o layout
            $viewContent = file_get_contents($viewFile);
            
        // Verifica se há @extends e processa o layout
        if (preg_match('/@extends\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $viewContent, $matches)) {
            $this->currentLayout = $matches[1];
                $layoutFile = $this->viewPath . '/' . $this->currentLayout . '.elle.php';
            
            // Se o layout não existe, lança exceção
                if (!file_exists($layoutFile)) {
                    throw new \Exception("Layout {$this->currentLayout} não encontrado");
                }
            
            // Verifica se o arquivo de cache precisa ser atualizado
            if ($this->needsRecompilation($viewFile, $cacheFile)) {
                // Primeiro processa as seções
                $this->processSections($viewContent);
                
                // Processa o layout
                $layoutContent = file_get_contents($layoutFile);
                $compiled = $this->parseLayout($layoutContent);

                // Adiciona as seções ao início do arquivo compilado
                $compiled = '<?php $__sections = ' . var_export($this->sections, true) . '; ?>' . $compiled;

                if (file_put_contents($cacheFile, $compiled) === false) {
                    throw new \Exception("Não foi possível escrever no arquivo de cache: {$cacheFile}");
                }
            }
        } else {
            // Se não há layout, verifica apenas a view
            if ($this->needsRecompilation($viewFile, $cacheFile)) {
                $compiled = $this->parseDirectives($viewContent);

            if (file_put_contents($cacheFile, $compiled) === false) {
                throw new \Exception("Não foi possível escrever no arquivo de cache: {$cacheFile}");
                }
            }
        }

        return $cacheFile;
    }

    private function parseDirectives(string $content): string
    {
        // Remove comentários {{-- --}} antes de processar outras diretivas
        $content = preg_replace('/\{\{--(.*?)--\}\}/s', '', $content);

        // Primeiro, processa expressões com operador de coalescência nula
        $content = preg_replace_callback(
            '/\{\{\s*\$([a-zA-Z0-9_]+)\s*\?\?\s*([^}]+)\}\}/',
            function($matches) {
                $var = trim($matches[1]);
                $fallback = trim($matches[2]);
                return '<?php echo htmlspecialchars(isset($' . $var . ') ? $' . $var . ' : ' . $fallback . '); ?>';
            },
            $content
        );

        // Depois processa as outras diretivas
        $patterns = [
            // {{ $variavel }}
            '/\{\{\s*\$([a-zA-Z0-9_]+)\s*\}\}/' => '<?php echo htmlspecialchars($$1 ?? ""); ?>',
            
            // @if(condição)
            '/@if\s*\((.*?)\)/' => '<?php if($1): ?>',
            
            // @elseif(condição)
            '/@elseif\s*\((.*?)\)/' => '<?php elseif($1): ?>',
            
            // @else
            '/@else/' => '<?php else: ?>',
            
            // @endif
            '/@endif/' => '<?php endif; ?>',
            
            // @foreach($array as $item)
            '/@foreach\s*\(\s*\$([a-zA-Z0-9_]+)\s+as\s+\$([a-zA-Z0-9_]+)\s*\)/' => '<?php foreach($$1 ?? [] as $$2): ?>',
            
            // @endforeach
            '/@endforeach/' => '<?php endforeach; ?>',
            
            // @include('view')
            '/@include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/' => '<?php include $this->compile("$1", get_defined_vars()); ?>',

            // @for(condição)
            '/@for\s*\((.*?)\)/' => '<?php for($1): ?>',
            
            // @endfor
            '/@endfor/' => '<?php endfor; ?>',
            
            // @while(condição)
            '/@while\s*\((.*?)\)/' => '<?php while($1): ?>',
            
            // @endwhile
            '/@endwhile/' => '<?php endwhile; ?>',
            
            // @unless(condição)
            '/@unless\s*\((.*?)\)/' => '<?php if(!($1)): ?>',
            
            // @endunless
            '/@endunless/' => '<?php endif; ?>',
            
            // @isset(variável)
            '/@isset\s*\((.*?)\)/' => '<?php if(isset($1)): ?>',
            
            // @endisset
            '/@endisset/' => '<?php endif; ?>',
            
            // @empty(variável)
            '/@empty\s*\((.*?)\)/' => '<?php if(empty($1)): ?>',
            
            // @endempty
            '/@endempty/' => '<?php endif; ?>',
            
            // @switch(variável)
            '/@switch\s*\((.*?)\)/' => '<?php switch($1): ?>',
            
            // @case(valor)
            '/@case\s*\((.*?)\)/' => '<?php case $1: ?>',
            
            // @break
            '/@break/' => '<?php break; ?>',
            
            // @default
            '/@default/' => '<?php default: ?>',
            
            // @endswitch
            '/@endswitch/' => '<?php endswitch; ?>',
            
            // @continue
            '/@continue/' => '<?php continue; ?>',
            
            // @break
            '/@break/' => '<?php break; ?>'
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $content);
    }

    private function processSections(string $content): void
    {
        // Processa @extends
        if (preg_match('/@extends\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $matches)) {
            $this->currentLayout = $matches[1];
        }

        // Processa @section com conteúdo
        preg_match_all('/@section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)(.*?)@endsection/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $name = $match[1];
            $sectionContent = trim($match[2]);
            
            // Processa o conteúdo da seção antes de armazenar
            ob_start();
            // Extrai as variáveis para o escopo atual
            extract($this->data);
            // Executa o código com as variáveis disponíveis
            eval('?>' . $this->parseDirectives($sectionContent));
            $processedContent = ob_get_clean();
            
            $this->sections[$name] = $processedContent;
        }

        // Processa @section com valor padrão (como no caso do title)
        preg_match_all('/@section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]\s*\)/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $name = $match[1];
            $default = $match[2];
            if (!isset($this->sections[$name])) {
                $this->sections[$name] = $default;
            }
        }

        // Remove todas as seções do conteúdo original
        $content = preg_replace('/@section\s*\(\s*[\'"][^\'"]+[\'"]\s*(?:,\s*[\'"][^\'"]*[\'"])?\s*\)(.*?)@endsection/s', '', $content);
        $content = preg_replace('/@section\s*\(\s*[\'"][^\'"]+[\'"]\s*,\s*[\'"][^\'"]*[\'"]\s*\)/', '', $content);
    }

    private function parseLayout(string $content): string
    {
        // Processa todas as diretivas do layout, incluindo variáveis e expressões
        $patterns = [
            // {{ $variavel ?? expressao }}
            '/\{\{\s*\$([a-zA-Z0-9_]+)\s*\?\?\s*([^}]+)\}\}/' => function($matches) {
                $var = trim($matches[1]);
                $fallback = trim($matches[2]);
                return '<?php echo htmlspecialchars(isset($' . $var . ') ? $' . $var . ' : ' . $fallback . '); ?>';
            },
            
            // {{ $variavel }}
            '/\{\{\s*\$([a-zA-Z0-9_]+)\s*\}\}/' => '<?php echo htmlspecialchars($$1 ?? ""); ?>',
            
            // @yield('nome', 'valor padrão')
            '/@yield\s*\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*[\'"]([^\'"]*)[\'"])?\s*\)/' => function($matches) {
                $name = $matches[1];
                $default = $matches[2] ?? '';
                return '<?php echo isset($__sections["' . $name . '"]) ? $__sections["' . $name . '"] : "' . $default . '"; ?>';
            }
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (is_callable($replacement)) {
                $content = preg_replace_callback($pattern, $replacement, $content);
            } else {
                $content = preg_replace($pattern, $replacement, $content);
            }
        }
        
        return $content;
    }
} 