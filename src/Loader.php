<?php

/**
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2020 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Designcise\ManifestJson;

use RuntimeException;
use InvalidArgumentException;

use function file_get_contents;
use function realpath;
use function json_decode;
use function array_filter;
use function pathinfo;

use const JSON_THROW_ON_ERROR;
use const ARRAY_FILTER_USE_KEY;
use const PATHINFO_EXTENSION;

class Loader
{
    /** @var string */
    private const MANIFEST_FILE_NAME = 'manifest.json';

    private array $metadata;

    private array $typedMetaData;

    /**
     * @param string $dir
     *
     * @throws RuntimeException
     * @throws \JsonException
     */
    public function __construct(string $dir)
    {
        $filePath = $this->createFilePathByDirectory($dir);
        $this->metadata = $this->getParsedMetadata($filePath);
    }

    public function has(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    public function get(string $key): string
    {
        if (! isset($this->metadata[$key])) {
            throw new InvalidArgumentException(
                'Manifest key ' . $key . ' does not exist.'
            );
        }

        return $this->metadata[$key];
    }

    public function getAll(): array
    {
        return $this->metadata;
    }

    public function getAllByType(string $type): array
    {
        if (isset($this->typedMetaData[$type])) {
            return $this->typedMetaData[$type];
        }

        $this->typedMetaData[$type] = array_filter($this->metadata, fn (string $fileName) => (
            $type === pathinfo($fileName, PATHINFO_EXTENSION)
        ), ARRAY_FILTER_USE_KEY);

        return $this->typedMetaData[$type];
    }

    public function getAllByTypes(array $types): array
    {
        $manifest = [];

        foreach ($types as $type) {
            $manifest[$type] = $this->getAllByType($type);
        }

        return $manifest;
    }

    /**
     * @param string $filePath
     *
     * @return array
     *
     * @throws \JsonException
     */
    private function getParsedMetadata(string $filePath): array
    {
        $fileContents = file_get_contents($filePath);

        return json_decode($fileContents, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $dir
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function createFilePathByDirectory(string $dir): string
    {
        $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
        $filePath = realpath($dir . DIRECTORY_SEPARATOR . self::MANIFEST_FILE_NAME);

        if ($filePath === false) {
            throw new RuntimeException(
                $filePath . ' does not exist.'
            );
        }
        return $filePath;
    }
}
