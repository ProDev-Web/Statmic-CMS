<?php

namespace Statamic\Updater\Presenters;

use Statamic\API\Str;

class GithubReleasePresenter
{
    /**
     * @var string
     */
    private $githubRelease;

    /**
     * Instantiate github release presenter.
     *
     * @param string $githubRelease
     */
    public function __construct(string $githubRelease)
    {
        $this->githubRelease = $githubRelease;
    }

    /**
     * Convert github release to HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        $string = markdown($this->githubRelease ?: '- [na] Changelog unavailable.');

        // TODO: Create tailwind classes for these labels.
        $string = Str::replace($string, '[new]', '<span class="label block text-center text-white rounded" style="background: #5bc0de; padding: 2px; padding-bottom: 1px;">NEW</span>');
        $string = Str::replace($string, '[fix]', '<span class="label block text-center text-white rounded" style="background: #5cb85c; padding: 2px; padding-bottom: 1px;">NEW</span>');
        $string = Str::replace($string, '[break]', '<span class="label block text-center text-white rounded" style="background: #d9534f; padding: 2px; padding-bottom: 1px;">NEW</span>');

        return $string;
    }

    /**
     * Output to HTML when cast as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }
}
