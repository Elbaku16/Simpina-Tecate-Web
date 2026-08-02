<?php
declare(strict_types=1);

interface DrawingStorage
{
    public function driver(): string;
    public function put(string $key, string $bytes, string $contentType): void;
    public function delete(string $key): void;
    public function exists(string $key): bool;
    public function size(string $key): ?int;
    public function temporaryUrl(string $key, int $seconds): ?string;
    public function localPath(string $key): ?string;
}
