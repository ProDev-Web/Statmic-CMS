<?php

namespace Statamic\View\Antlers;

class Antlers
{
    public function parser()
    {
        return (new Parser)
            ->callback(['Statamic\View\Antlers\Engine', 'renderTag']);
    }

    public function parse($str, $variables = [])
    {
        $parser = $this->parser();

        if (! is_array($variables)) {
            $variables = $variables->toArray();
        }

        return $parser->parse($str, $variables);
    }

    /**
     * Iterate over an array and parse the string/template for each
     *
     * @param string  $content
     * @param array   $data
     * @param bool    $supplement
     * @param array   $context
     * @return string
     */
    public function parseLoop($content, $data, $supplement, $context)
    {
        $output = '';
        $i      = 1;
        $total  = count($data);

        foreach ($data as $item) {
            if ($supplement) {
                $item['first']         = ($i === 1);
                $item['last']          = ($i === $total);
                $item['zero_index']    = $i - 1;
                $item['index']         = $i;
                $item['count']         = $i;
                $item['total_results'] = $total;
            }

            $output .= $this->parse($content, array_merge($context, $item));
            $i++;
        }

        return $output;
    }
}
