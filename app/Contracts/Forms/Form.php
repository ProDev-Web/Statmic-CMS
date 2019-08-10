<?php

namespace Statamic\Contracts\Forms;

use Statamic\Fields\Blueprint;
use Statamic\Contracts\CP\Editable;
use Illuminate\Contracts\Support\Arrayable;

interface Form extends Arrayable, Editable
{
    /**
     * Get or set the handle
     *
     * @param  string|null $name
     * @return string
     */
    public function handle($handle = null);

    /**
     * Get or set the title
     *
     * @param  string|null $title
     * @return string
     */
    public function title($title = null);

    /**
     * Get or set the Formset
     *
     * @param  Formset|null $formset
     * @return Formset
     */
    public function blueprint(Blueprint $blueprint = null);

    /**
     * Get the submissions
     *
     * @return Illuminate\Support\Collection
     */
    public function submissions();

    /**
     * Get a submission
     *
     * @param  string $id
     * @return Submission
     */
    public function submission($id);

    /**
     * Create a submission
     *
     * @return Submission
     */
    public function createSubmission();

    /**
     * Delete a submission
     *
     * @return boolean
     */
    public function deleteSubmission($id);

    /**
     * Get or set the honeypot field
     *
     * @param  string|null $honeypot
     * @return string
     */
    public function honeypot($honeypot = null);

    /**
     * Get all the metrics
     *
     * @param array|null $metrics
     * @return array
     */
    public function metrics($metrics = null);

    /**
     * Get or set the email config
     *
     * @param  array|null $email
     * @return array
     */
    public function email($email = null);

    /**
     * Save the form
     *
     * @return void
     */
    public function save();
}
