<?php

namespace Tests\Yaml;

use Exception;
use Tests\TestCase;
use Statamic\Facades\YAML;
use Statamic\Yaml\ParseException;

class YamlTest extends TestCase
{
    /** @test */
    function it_dumps_yaml()
    {
        $array = [
            'foo' => 'bar',
            'two_words' => 'two words',
            'multiline' => "first\nsecond",
            'array' => ['one', 'two'],
        ];

        $expected = <<<EOT
foo: bar
two_words: 'two words'
multiline: |
  first
  second
array:
  - one
  - two

EOT;

        $this->assertEquals($expected, YAML::dump($array));
    }

    /** @test */
    function it_dumps_with_front_matter_when_content_is_passed()
    {
        $expected = <<<EOT
---
foo: bar
---
some content
EOT;

        $this->assertEquals($expected, YAML::dump(['foo' => 'bar'], 'some content'));
    }

    /** @test */
    function it_explicitly_dumps_front_matter()
    {
        $expected = <<<EOT
---
foo: bar
---

EOT;

        $this->assertEquals($expected, YAML::dumpFrontMatter(['foo' => 'bar']));
    }

    /** @test */
    function it_explicitly_dumps_front_matter_with_content()
    {
        $expected = <<<EOT
---
foo: bar
---
some content
EOT;

        $this->assertEquals($expected, YAML::dumpFrontMatter(['foo' => 'bar'], 'some content'));
    }

    /** @test */
    function it_parses_a_string_of_yaml()
    {
        $this->assertEquals(['foo' => 'bar'], YAML::parse('foo: bar'));
    }

    /** @test */
    function it_parses_an_empty_string_of_yaml()
    {
        $this->assertEquals([], YAML::parse(''));
    }

    /** @test */
    function it_parses_with_content_and_front_matter()
    {
        $yaml = <<<EOT
---
foo: bar
---
some content
EOT;

        $this->assertEquals(['foo' => 'bar', 'content' => 'some content'], YAML::parse($yaml));
    }

    /** @test */
    function it_parses_a_file_when_no_argument_is_given()
    {
        $yaml = <<<EOT
---
foo: bar
---
some content
EOT;

        $fp = tmpfile();
        fwrite($fp, $yaml);
        $path = stream_get_meta_data($fp)['uri'];

        $this->assertEquals(
            ['foo' => 'bar', 'content' => 'some content'],
            YAML::file($path)->parse()
        );
    }

    /** @test */
    function it_throws_exception_when_parsing_without_an_argument_or_file()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot parse YAML without a file or string.');

        YAML::parse();
    }

    /** @test */
    function it_creates_parse_exception_pointing_to_temporary_file_when_no_file_is_provided()
    {
        $yaml = <<<EOT
---
foo: 'bar
baz: 'qux'
---
some content
EOT;

        try {
            YAML::parse($yaml);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unexpected characters near "qux\'" at line 3 (near "baz: \'qux\'").', $e->getMessage());
            $path = storage_path('statamic/tmp/yaml-'.md5("---\nfoo: 'bar\nbaz: 'qux'"));
            $this->assertEquals($path, $e->getFile());
            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    function it_creates_parse_exception_pointing_to_actual_file_when_file_is_provided()
    {
        $yaml = <<<EOT
---
foo: 'bar
baz: 'qux'
---
some content
EOT;

        try {
            YAML::file('path/to/file.yaml')->parse($yaml);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unexpected characters near "qux\'" at line 3 (near "baz: \'qux\'").', $e->getMessage());
            $this->assertEquals('path/to/file.yaml', $e->getFile());
            return;
        }

        $this->fail('Exception was not thrown.');
    }
}
