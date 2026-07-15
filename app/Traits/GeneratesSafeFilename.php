<?php

namespace App\Traits;

/**
 * Provides a helper to sanitize a person's name into a safe string
 * suitable for use in a download filename.
 *
 * Characters forbidden in Windows/Unix filenames are stripped.
 */
trait GeneratesSafeFilename
{
    /**
     * Strip filesystem-unsafe characters from a name string.
     * Returns an empty string if the input is blank.
     */
    protected function safeName(string $name): string
    {
        return str_replace(
            ['/', '\\', ':', '*', '?', '"', '<', '>', '|'],
            '',
            trim($name)
        );
    }

    /**
     * Build a full download filename from a display name and extension.
     * Falls back to $fallback (e.g. transaction number or ID) when the
     * sanitized name is empty.
     *
     * @param  string      $prefix    Label prefix, e.g. "Appointment Form - "
     * @param  string      $name      Raw display name to sanitize
     * @param  string      $extension File extension without the dot, e.g. "docx"
     * @param  string|int  $fallback  Value used when $name sanitizes to empty
     */
    protected function buildFilename(
        string     $prefix,
        string     $name,
        string     $extension,
        string|int $fallback = ''
    ): string {
        $safe = $this->safeName($name);

        return sprintf('%s%s.%s', $prefix, $safe ?: $fallback, $extension);
    }
}
