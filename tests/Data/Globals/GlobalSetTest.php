<?php

namespace Tests\Data\Globals;

use Tests\TestCase;
use Statamic\API\Site;
use Statamic\Data\Globals\GlobalSet;

class GlobalSetTest extends TestCase
{
    /** @test */
    function it_gets_file_contents_for_saving_with_a_single_site()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            ]
        ]);

        $set = (new GlobalSet)
            ->id('123')
            ->title('The title');

        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string'
            ]);
        });

        $expected = <<<'EOT'
id: '123'
title: 'The title'
data:
  array:
    - 'first one'
    - 'second one'
  string: 'The string'

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }

    /** @test */
    function it_gets_file_contents_for_saving_with_multiple_sites()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/']
            ]
        ]);

        $set = (new GlobalSet)
            ->id('123')
            ->title('The title')
            ->sites(['en', 'fr']);

        // We set the data but it's basically irrelevant since it won't get saved to this file.
        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string'
            ]);
        });
        $set->in('fr', function ($loc) {
            $loc->data([
                'array' => ['le first one', 'le second one'],
                'string' => 'Le string'
            ]);
        });

        $expected = <<<'EOT'
id: '123'
title: 'The title'
sites:
  - en
  - fr

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }
}
