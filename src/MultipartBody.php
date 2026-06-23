<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * multipart/form-data istek gövdesi.
 *
 * Düz veri (metin alanları + dosya parçaları) tutar; Guzzle'ın `multipart` istek
 * seçeneği için uygun diziyi üretir. Dosya yükleyen endpoint'ler için kullanılır.
 */
final class MultipartBody
{
    /** @var array<int,array{name:string,value:string}> */
    private array $fields = [];

    /** @var array<int,array{name:string,filename:string,content:string,contentType:string}> */
    private array $files = [];

    /** Metin form alanı ekler. */
    public function addField(string $name, string $value): self
    {
        $this->fields[] = ['name' => $name, 'value' => $value];
        return $this;
    }

    /** Dosya parçası ekler. İçerik ham bayt dizesidir. */
    public function addFile(string $name, string $filename, string $content, string $contentType = 'application/octet-stream'): self
    {
        $this->files[] = ['name' => $name, 'filename' => $filename, 'content' => $content, 'contentType' => $contentType];
        return $this;
    }

    /**
     * Guzzle'ın `multipart` seçeneği için parça dizisi üretir.
     *
     * @return array<int,array<string,mixed>>
     */
    public function toGuzzleParts(): array
    {
        $parts = [];
        foreach ($this->fields as $f) {
            $parts[] = ['name' => $f['name'], 'contents' => $f['value']];
        }
        foreach ($this->files as $file) {
            $parts[] = [
                'name' => $file['name'],
                'contents' => $file['content'],
                'filename' => $file['filename'],
                'headers' => ['Content-Type' => $file['contentType']],
            ];
        }
        return $parts;
    }
}
