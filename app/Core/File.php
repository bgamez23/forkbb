<?php

namespace ForkBB\Core;

use ForkBB\Core\Exceptions\FileException;
use InvalidArgumentException;

class File
{
    /**
     * Текст ошибки
     * @var null|string
     */
    protected $error;

    /**
     * Путь до файла
     * @var null|string
     */
    protected $path;

    /**
     * Содержимое файла
     * @var null|string
     */
    protected $data;

    /**
     * Оригинальное имя файла без расширения
     * @var null|string
     */
    protected $name;

    /**
     * Оригинальное расширение файла
     * @var null|string
     */
    protected $ext;

    /**
     * Размер оригинального файла
     */
    protected $size;

    /**
     * Флаг автопереименования файла
     * @var bool
     */
    protected $rename  = false;

    /**
     * Флаг перезаписи файла
     * @var bool
     */
    protected $rewrite = false;

    /**
     * Паттерн для pathinfo
     * @var string
     */
    protected $pattern = '%^(?!.*?\.\.)([\w.\x5C/:-]*[\x5C/])?(\*|[\w.-]+)\.(\*|[a-z\d]+)$%i';

    /**
     * Конструктор
     *
     * @param string $path
     * @param array $options
     *
     * @throws FileException
     */
    public function __construct($path, $options)
    {
        if (! \is_file($path)) {
            throw new FileException('File not found');
        }
        if (! \is_readable($path)) {
            throw new FileException('File can not be read');
        }

        $this->path = $path;
        $this->data = null;

        $name = null;
        $ext  = null;
        if (isset($options['basename'])) {
            if (false === ($pos = \strrpos($options['basename'], '.'))) {
                $name = $options['basename'];
            } else {
                $name = \substr($options['basename'], 0, $pos);
                $ext  = \substr($options['basename'], $pos + 1);
            }
        }

        $this->name = isset($options['filename']) && \is_string($options['filename']) ? $options['filename'] : $name;
        $this->ext  = isset($options['extension']) && \is_string($options['extension']) ? $options['extension'] : $ext;

        $this->size = \is_string($this->data) ? \strlen($this->data) : \filesize($path);
        if (! $this->size) {
            throw new FileException('File size is undefined');
        }
    }

    /**
     * Возвращает текст ошибки
     *
     * @return null|string
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Фильрует и переводит в латиницу(?) имя файла
     *
     * @param string $name
     *
     * @return string
     */
    protected function filterName($name)
    {
        if (\function_exists('\transliterator_transliterate')) {
            $name = \transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $name);
        }

        $name = \trim(\preg_replace('%[^\w.-]+%', '-', $name), '-');

        if (! isset($name{0})) {
            $name = (string) \time();
        }

        return $name;
    }

    /**
     * Возвращает информацию о пути к сохраняемому файлу с учетом подстановок
     *
     * @param string $path
     *
     * @return false|array
     */
    protected function pathinfo($path)
    {
        if (! \preg_match($this->pattern, $path, $matches)) {
            $this->error = 'The path/name format is broken';
            return false;
        }

        if ('*' === $matches[2]) {
            $matches[2] = $this->filterName($this->name);
        }

        if ('*' === $matches[3]) {
            $matches[3] = $this->ext;
        } elseif ('(' === $matches[3]{0} && ')' === $matches[3]{\strlen($matches[3]) - 1}) {
            $matches[3] = \explode('|', \substr($matches[3], 1, -1));

            if (1 === \count($matches[3])) {
                $matches[3] = \array_pop($matches[3]);
            }
        }

        return [
            'dirname'   => $matches[1],
            'filename'  => $matches[2],
            'extension' => $matches[3],
        ];
    }

    /**
     * Устанавливает флаг автопереименования файла
     *
     * @param bool $rename
     *
     * @return File
     */
    public function rename($rename)
    {
        $this->rename = $rename;

        return $this;
    }

    /**
     * Устанавливает флаг перезаписи файла
     *
     * @param bool $rewrite
     *
     * @return File
     */
    public function rewrite($rewrite)
    {
        $this->rewrite = $rewrite;

        return $this;
    }

    /**
     * Создает/проверяет на запись директорию
     *
     * @param string $dirname
     *
     * @return bool
     */
    protected function dirProc($dirname)
    {
        if (! \is_dir($dirname)) {
            if (! @\mkdir($dirname, 0755)) {
                $this->error = 'Can not create directory';
                return false;
            }
        }
        if (! \is_writable($dirname)) {
            $this->error = 'No write access for directory';
            return false;
        }

        return true;
    }

    /**
     * Создает/устанавливает права на файл
     *
     * @param string $path
     *
     * @return bool
     */
    protected function fileProc($path)
    {
        if (\is_string($this->data)) {
            if (! \file_put_contents($this->path, $path)) {
                $this->error = 'Error writing file';
                return false;
            }
        } else {
            if (! \copy($this->path, $path)) {
                $this->error = 'Error copying file';
                return false;
            }
        }
        @\chmod($path, 0644);

        return true;
    }

    /**
     * Сохраняет файл по указанному шаблону пути
     *
     * @param string $path
     *
     * @return bool
     */
    public function toFile($path)
    {
        $info = $this->pathinfo($path);

        if (false === $info || ! $this->dirProc($info['dirname'])) {
            return false;
        }

        if ($this->rename) {
            $old = $info['filename'];
            $i   = 1;
            while (\file_exists($info['dirname'] . $info['filename'] . '.' . $info['extension'])) {
                ++$i;
                $info['filename'] = $old . '_' . $i;
            }
        } elseif (! $this->rewrite && \file_exists($info['dirname'] . $info['filename'] . '.' . $info['extension'])) {
            $this->error = 'Such file already exists';
            return false;
        }

        $path = $info['dirname'] . $info['filename'] . '.' . $info['extension'];

        if ($this->fileProc($path)) {
            $this->path = $path;
            $this->name = $info['filename'];
            $this->ext  = $info['extension'];
            $this->size = \filesize($path);
            return true;
        } else {
            return false;
        }
    }

    public function name()
    {
        return $this->name;
    }

    public function ext()
    {
        return $this->ext;
    }

    public function size()
    {
        return $this->size;
    }

    public function path()
    {
        return $this->path;
    }
}