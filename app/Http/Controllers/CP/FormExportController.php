<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Str;
use Statamic\API\Form;
use Statamic\API\File;
use Statamic\Exceptions\FatalException;

class FormExportController extends CpController
{
    public function export($form, $type)
    {
        $this->access('forms');

        $form = Form::get($form);

        $exporter = 'Statamic\Forms\Exporters\\' . Str::studly($type) . 'Exporter';

        if (! class_exists($exporter)) {
            throw new FatalException("Exporter of type [$type] does not exist.");
        }

        $exporter = new $exporter;
        $exporter->form($form);

        $content = $exporter->export();

        if ($this->request->has('download')) {
            $path = temp_path('forms/'.$form->name().'-'.time().'.'.$type);
            File::put($path, $content);
            $response = response()->download($path)->deleteFileAfterSend(true);
        } else {
            $response = response($content)->header('Content-Type', $exporter->contentType());
        }

        return $response;
    }
}
