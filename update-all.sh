#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Função para fazer backup
backup_files() {
    echo -e "${BLUE}Fazendo backup dos arquivos locais...${NC}"
    timestamp=$(date +%Y%m%d_%H%M%S)
    backup_dir="backup_${timestamp}"
    mkdir -p "$backup_dir"
    
    # Backup das migrations
    if [ -d "database/migrations" ]; then
        cp -r database/migrations "$backup_dir/"
    fi
    
    # Backup de outros arquivos importantes
    if [ -f ".env" ]; then
        cp .env "$backup_dir/"
    fi
    
    echo -e "${GREEN}Backup criado em $backup_dir${NC}"
}

# Verifica se há alterações locais não commitadas
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}Atenção: Existem alterações locais não commitadas!${NC}"
    echo -e "${YELLOW}Deseja fazer backup antes de continuar? (s/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Ss]$ ]]; then
        backup_files
    fi
fi

echo -e "${BLUE}Atualizando o framework principal...${NC}"
git pull origin main

echo -e "${BLUE}Atualizando o frontend...${NC}"
cd frontend
git pull origin main
cd ..

echo -e "${BLUE}Copiando arquivos estáticos para public...${NC}"
cp -r frontend/dist-server/* public/

echo -e "${GREEN}Atualização concluída com sucesso!${NC}"

# Verifica se há conflitos
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}Atenção: Existem alterações locais após a atualização.${NC}"
    echo -e "${YELLOW}Verifique os arquivos modificados com 'git status'${NC}"
fi 