<?php namespace Anomaly\EditorFieldType;

use Anomaly\Streams\Platform\Application\Application;
use Anomaly\Streams\Platform\Entry\Contract\EntryInterface;
use Anomaly\Streams\Platform\Entry\EntryTranslationsModel;
use Illuminate\Filesystem\Filesystem;

/**
 * Class EditorFieldTypeStorage
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class EditorFieldTypeStorage
{

    /**
     * The editor field type.
     *
     * @var EditorFieldType
     */
    protected $fieldType;

    /**
     * The application instance.
     *
     * @var Application
     */
    protected $application;

    /**
     * The filesystem.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new EditorFieldTypeStorage instance.
     *
     * @param EditorFieldType $fieldType
     * @param Application     $application
     * @param Filesystem      $files
     */
    public function __construct(EditorFieldType $fieldType, Application $application, Filesystem $files)
    {
        $this->files       = $files;
        $this->fieldType   = $fieldType;
        $this->application = $application;
    }

    /**
     * Get the directory.
     *
     * @return null|string
     */
    public function directory()
    {
        $entry = $this->fieldType->getEntry();

        /**
         * We must have an entry with ID.
         */
        if ($entry === null || !is_object($entry) || !$entry->getId()) {
            return null;
        }

        /**
         * We must have an entry / translation.
         */
        if (!$entry instanceof EntryInterface && !$entry instanceof EntryTranslationsModel) {
            return null;
        }

        /**
         * By default name the directory after
         * the stream namespace, slug and entry ID.
         */
        $slug      = $entry->getStreamSlug();
        $namespace = $entry->getStreamNamespace();
        $directory = $entry->getEntryId();

        return implode(
            DIRECTORY_SEPARATOR,
            [
                $namespace,
                $slug,
                $directory,
            ]
        );
    }

    /**
     * Return the path.
     *
     * @return string
     */
    public function path()
    {
        $directory = $this->directory();
        $filename  = $this->filename();

        if (!$directory || !$filename) {
            return null;
        }

        return implode(
            DIRECTORY_SEPARATOR,
            [
                $directory,
                $filename
            ]
        );
    }

    /**
     * Get the storage path.
     *
     * @return null|string
     */
    public function storagePath()
    {
        if (!$path = $this->path()) {
            return null;
        }

        return $this->application->getStoragePath($path);
    }

    /**
     * Get the view path.
     *
     * @return string
     */
    public function viewPath()
    {
        if (!$path = $this->path()) {
            return null;
        }

        return 'storage::' . str_replace(['.html', '.twig'], '', $path);
    }

    /**
     * Get the asset path.
     *
     * @return string
     */
    public function assetPath()
    {
        if (!$path = $this->path()) {
            return null;
        }

        return 'storage::' . $path;
    }

    /**
     * Get the storage filename.
     *
     * @return string
     */
    public function filename()
    {
        $entry = $this->fieldType->getEntry();

        /**
         * We must have an entry with ID like upper in `directories()`
         */
        if ($entry === null || !is_object($entry) || !$entry->getId()) {
            return null;
        }

        return trim(
                $this->fieldType->getField() . '_' . $this->fieldType->getLocale(),
                '_'
            ) . '.' . $this->fieldType->extension();
    }

    /**
     * Cleanup the storage file.
     */
    public function cleanup()
    {
        $path = $this->fieldType->getStoragePath();

        if ($path && $this->files->exists($path)) {
            $this->files->delete($path);
        }
    }
}
