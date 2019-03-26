<?php

namespace Statamic\Revisions;

use Illuminate\Support\Carbon;
use Facades\Statamic\Revisions\Repository as Revisions;

trait Revisable
{
    public function revisions()
    {
        return Revisions::whereKey($this->revisionKey());
    }

    public function latestRevision()
    {
        return $this->revisions()->last();
    }

    public function makeRevision()
    {
        return (new Revision)
            ->date(Carbon::now())
            ->key($this->revisionKey())
            ->attributes($this->revisionAttributes());
    }

    public function makeWorkingCopy()
    {
        return (new WorkingCopy)
            ->date(Carbon::now())
            ->key($this->revisionKey())
            ->attributes($this->revisionAttributes());
    }

    public function fromWorkingCopy()
    {
        return $this->makeFromRevision(
            Revisions::findWorkingCopyByKey($this->revisionKey())
        );
    }

    abstract protected function revisionKey();
    abstract protected function revisionAttributes();
    abstract protected function makeFromRevision($revision);
}
