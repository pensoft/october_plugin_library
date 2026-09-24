<?php namespace Pensoft\Library\Models;

use Model;

/**
 * Library settings (Backend > Settings > Library).
 */
class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'pensoft_library_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    /**
     * Whether the frontend library filter shows the "Target audience" select.
     * Off by default.
     */
    public static function showTargetFilter()
    {
        return (bool) static::get('show_target_filter', false);
    }
}
