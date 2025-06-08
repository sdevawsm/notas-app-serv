<?php

namespace App\Services;

class UserService {
    public function getAll(): array {}
    public function find(int $id) {}
    public function create(array $data) {}
    public function update(int $id, array $data): bool {}
    public function delete(int $id): bool {}
}
