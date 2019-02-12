<?php

namespace Statamic\Preferences;

use Illuminate\Support\Arr;

trait HasPreferences
{
    protected $preferences = [];

    /**
     * Get or set preferences.
     *
     * @param null|array $preferences
     * @return $this
     */
    public function preferences($preferences = null)
    {
        if (is_null($preferences)) {
            return $this->preferences;
        }

        $this->preferences = $preferences;

        return $this;
    }

    /**
     * Add/merge array of preferences.
     *
     * @param array $preferences
     * @return $this
     */
    public function addPreferences($preferences)
    {
        $this->preferences = array_merge($this->preferences, Arr::wrap($preferences));

        return $this;
    }

    /**
     * Add preference (dot notation in key supported).
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addPreference($key, $value)
    {
        Arr::set($this->preferences, $key, $value);

        return $this;
    }

    /**
     * Remove preference (dot notation in key supported).
     *
     * @param string $key
     * @return $this
     */
    public function removePreference($key)
    {
        Arr::pull($this->preferences, $key);

        return $this;
    }

    /**
     * Get preference (dot notation in key supported).
     *
     * @param string $key
     * @return mixed
     */
    public function getPreference($key)
    {
        return Arr::get($this->preferences, $key);
    }

    /**
     * Check if preference exists (dot notation in key supported).
     *
     * @param string $key
     * @return bool
     */
    public function hasPreference($key)
    {
        return (bool) $this->getPreference($key);
    }
}
