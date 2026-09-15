<?php namespace Pensoft\Library\Classes;

use Str;
use Config;
use Response;
use System\Models\File;
use Cms\Classes\Controller as CmsController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Friendly download links for attached files:
 *
 *   /<prefix>/<file id>/<slug of the original file name>.<ext>
 *
 * The file is sent with its original file name. Uploads stored with a hashed original
 * name (e.g. 685bfa1e19a1e798863423.png) are named after their item title + file label.
 *
 * Which attachments may be served is configured in `download.models` (model class =>
 * attachment fields) — the plugin default allows Library files only; a site can add
 * other models via config/pensoft/library/config.php.
 *
 * Twig: download_url(file, item) / download_name(file, item) — see Plugin::registerMarkupTags().
 */
class DownloadLink
{
    /**
     * url returns the friendly download URL for a file, or '' when there is no file
     */
    public static function url($file, $item = null)
    {
        if (!$file instanceof File) {
            return '';
        }

        return url(static::prefix() . '/' . $file->id . '/' . static::slugName($file, $item));
    }

    /**
     * name returns the file name the download is saved as
     */
    public static function name($file, $item = null)
    {
        if (!$file instanceof File) {
            return '';
        }

        return static::parts($file, $item)[2];
    }

    /**
     * prefix returns the route prefix, "download" by default
     */
    public static function prefix()
    {
        return trim((string) Config::get('pensoft.library::download.prefix', 'download'), '/');
    }

    /**
     * response serves the download route: 404 for unknown / non-public / not allowed files,
     * 301 to the canonical URL for a wrong name, otherwise the file itself
     */
    public static function response($id, $name = null)
    {
        $file = File::find((int) $id);

        if (!$file || !$file->isPublic() || !static::isAllowed($file)) {
            return static::notFound();
        }

        if ($name !== static::slugName($file)) {
            return redirect(static::url($file), 301);
        }

        $fileName = str_replace(['/', '\\'], '_', static::name($file));
        $fallback = preg_replace('/[^\x20-\x7e]|[%"]/', '_', Str::ascii($fileName));

        return Response::download($file->getLocalPath(), $fileName, [
            'Content-Type' => $file->getContentType(),
            'X-Robots-Tag' => 'noindex, nofollow',
        ])->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName, $fallback);
    }

    /**
     * isAllowed checks the file is attached to a configured model field
     */
    public static function isAllowed(File $file)
    {
        $models = [];
        foreach ((array) Config::get('pensoft.library::download.models', []) as $class => $fields) {
            $models[strtolower(ltrim($class, '\\'))] = (array) $fields;
        }

        $fields = $models[strtolower(ltrim((string) $file->attachment_type, '\\'))] ?? [];

        return in_array($file->field, $fields, true);
    }

    protected static function slugName(File $file, $item = null)
    {
        [$base, $ext] = static::parts($file, $item);

        return (Str::slug($base) ?: 'file') . ($ext ? '.' . $ext : '');
    }

    /**
     * parts returns [base name, lowercase extension, file name to send]
     */
    protected static function parts(File $file, $item = null)
    {
        $parts = explode('.', $file->file_name);
        $base = count($parts) > 1 ? implode('.', array_slice($parts, 0, -1)) : $file->file_name;
        $ext = strtolower($file->getExtension());
        $name = $file->file_name;

        // Hashed upload names, optionally with a copy suffix: "683d50234455f805118328 (1)"
        if (preg_match('/^[0-9a-f]{16,}( \(\d+\))?$/i', $base) && ($item = $item ?: $file->attachment)) {
            $label = $file->title ?: $file->description;
            $readable = trim(($item->title ?: $item->name) . ($label ? ' - ' . $label : ''));

            if ($readable !== '') {
                $base = $readable;
                $name = $base . ($ext ? '.' . $ext : '');
            }
        }

        return [$base, $ext, $name];
    }

    protected static function notFound()
    {
        $page = (new CmsController)->run('404');
        $content = $page instanceof \Symfony\Component\HttpFoundation\Response ? $page->getContent() : (string) $page;

        return Response::make($content, 404);
    }
}
