<?php
declare(strict_types=1);

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

final class R2DrawingStorage implements DrawingStorage
{
    private S3Client $client;
    private string $bucket;

    public function __construct()
    {
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_file($autoload)) {
            throw new RuntimeException('Faltan dependencias de Composer para R2.');
        }
        require_once $autoload;

        $account = trim((string) getenv('R2_ACCOUNT_ID'));
        $access = trim((string) getenv('R2_ACCESS_KEY_ID'));
        $secret = trim((string) getenv('R2_SECRET_ACCESS_KEY'));
        $this->bucket = trim((string) getenv('R2_BUCKET'));
        if ($account === '' || $access === '' || $secret === '' || $this->bucket === '') {
            throw new RuntimeException('La configuración de R2 está incompleta.');
        }

        $this->client = new S3Client([
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => "https://{$account}.r2.cloudflarestorage.com",
            'credentials' => new Credentials($access, $secret),
            'use_path_style_endpoint' => true,
        ]);
    }

    public function driver(): string { return 'r2'; }

    public function put(string $key, string $bytes, string $contentType): void
    {
        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $bytes,
            'ContentType' => $contentType,
            'CacheControl' => 'private, no-store',
        ]);
    }

    public function delete(string $key): void
    {
        $this->client->deleteObject(['Bucket' => $this->bucket, 'Key' => $key]);
    }

    public function exists(string $key): bool
    {
        return $this->size($key) !== null;
    }

    public function size(string $key): ?int
    {
        try {
            $result = $this->client->headObject(['Bucket' => $this->bucket, 'Key' => $key]);
            return isset($result['ContentLength']) ? (int) $result['ContentLength'] : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function temporaryUrl(string $key, int $seconds): ?string
    {
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
            'ResponseContentType' => 'image/png',
            'ResponseCacheControl' => 'private, no-store',
        ]);
        $request = $this->client->createPresignedRequest($command, '+' . max(1, min(300, $seconds)) . ' seconds');
        return (string) $request->getUri();
    }

    public function localPath(string $key): ?string { return null; }
}
