<?php

namespace Tests\View\Antlers;

use Tests\TestCase;
use Statamic\API\Antlers;
use Statamic\Fields\Value;
use Statamic\Fields\Fieldtype;
use Illuminate\Contracts\Support\Arrayable;

class ParserTest extends TestCase
{
    private $variables;

    public function setUp()
    {
        parent::setUp();

        $this->variables = [
            'string' => 'Hello wilderness',
            'simple' => ['one', 'two', 'three'],
            'complex' => [
                ['string' => 'the first string'],
                ['string' => 'the second string']
            ],
            'date' => 'June 19 2012',
            'content' => "Paragraph"
        ];
    }

    public function testStringVariable()
    {
        $template = "{{ string }}";

        $this->assertEquals('Hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testStringVariableWithTightBraces()
    {
        $template = "{{string}}";

        $this->assertEquals('Hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testListVariable()
    {
        $template = "{{ simple }}{{ value }}{{ /simple }}";

        $this->assertEquals('onetwothree', Antlers::parse($template, $this->variables));
    }

    public function testNonExistantVariablesShouldBeNull()
    {
        $template = "{{ missing }}";

        $this->assertEquals(null, Antlers::parse($template, $this->variables));
    }

    /** @test */
    function non_arrays_cannot_be_looped()
    {
        $template = "{{ string }} {{ /string }}";

        $this->assertEquals('', Antlers::parse($template, $this->variables));

        // TODO: Assert about log message
    }

    public function testStaticStringsWithDoubleQuotesShouldBeLeftAlone()
    {
        $template = '{{ "Thundercats are Go!" }}';

        $this->assertEquals("Thundercats are Go!", Antlers::parse($template, $this->variables));
    }

    public function testStaticStringsWithSingleQuotesShouldBeLeftAlone()
    {
        $template = "{{ 'Thundercats are Go!' }}";

        $this->assertEquals("Thundercats are Go!", Antlers::parse($template, $this->variables));
    }

    public function testStaticStringsWithDoubleQuotesCanBeModified()
    {
        $template = '{{ "Thundercats are Go!" | upper }}';

        $this->assertEquals("THUNDERCATS ARE GO!", Antlers::parse($template, $this->variables));
    }

    public function testStaticStringsWithSingleQuotesCanBeModified()
    {
        $template = "{{ 'Thundercats are Go!' | upper }}";

        $this->assertEquals("THUNDERCATS ARE GO!", Antlers::parse($template, $this->variables));
    }

    public function testSingleBracesShouldNotBeParsed()
    {
        $template = "{string}";

        $this->assertEquals('{string}', Antlers::parse($template, $this->variables));
    }

    public function testModifiedNonExistantVariablesShouldBeNull()
    {
        $template = "{{ missing|upper }}";

        $this->assertEquals(null, Antlers::parse($template, $this->variables));
    }

    public function testUnclosedArrayVariablePairsShouldBeNull()
    {
        $template = "{{ simple }}";

        $this->assertEquals(null, Antlers::parse($template, $this->variables));

        // TODO: Assert about log message
    }

    public function testSingleCondition()
    {
        $template = '{{ if string == "Hello wilderness" }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($template, $this->variables));
    }

    public function testMultipleAndConditions()
    {
        $template = '{{ if string == "Hello wilderness" && content }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($template, $this->variables));
    }

    public function testMultipleOrConditions()
    {
        $should_pass = '{{ if string == "failure" || string == "Hello wilderness" }}yes{{ endif }}';
        $should_fail = '{{ if string == "failure" or string == "womp" }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($should_pass, $this->variables));
        $this->assertEquals(null, Antlers::parse($should_fail, $this->variables));
    }

    public function testOrExistanceConditions()
    {
        $should_pass = '{{ if string || strudel }}yes{{ endif }}';
        $should_also_pass = '{{ if strudel or string }}yes{{ endif }}';
        $should_fail = '{{ if strudel || wurst }}yes{{ endif }}';
        $should_also_fail = '{{ if strudel or wurst }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($should_pass, $this->variables));
        $this->assertEquals('yes', Antlers::parse($should_also_pass, $this->variables));
        $this->assertEquals(null, Antlers::parse($should_fail, $this->variables));
        $this->assertEquals(null, Antlers::parse($should_also_fail, $this->variables));
    }

    public function testSingleStandardStringModifierTight()
    {
        $template = "{{ string|upper }}";

        $this->assertEquals('HELLO WILDERNESS', Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardStringModifiersTight()
    {
        $template = "{{ string|upper|lower }}";

        $this->assertEquals('hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testSingleStandardStringModifierRelaxed()
    {
        $template = "{{ string | upper }}";

        $this->assertEquals('HELLO WILDERNESS', Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardStringModifiersRelaxed()
    {
        $template = "{{ string | upper | lower }}";

        $this->assertEquals('hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testSingleParameterStringModifier()
    {
        $template = "{{ string upper='true' }}";

        $this->assertEquals('HELLO WILDERNESS', Antlers::parse($template, $this->variables));
    }

    public function testChainedParameterStringModifiers()
    {
        $template = "{{ string upper='true' lower='true' }}";

        $this->assertEquals('hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testSingleStandardArrayModifierTight()
    {
        $template = "{{ simple|length }}";

        $this->assertEquals(3, Antlers::parse($template, $this->variables));
    }

    public function testSingleStandardArrayModifierRelaxed()
    {
        $template = "{{ simple | length }}";

        $this->assertEquals(3, Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardArrayModifiersTightOnContent()
    {
        $template = "{{ content|markdown|lower }}";

        $this->assertEquals("<p>paragraph</p>".PHP_EOL, Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardModifiersRelaxedOnContent()
    {
        $template = "{{ content | markdown | lower }}";

        $this->assertEquals("<p>paragraph</p>".PHP_EOL, Antlers::parse($template, $this->variables));
    }

    public function testChainedParameterModifiersOnContent()
    {
        $template = "{{ content markdown='true' lower='true' }}";

        $this->assertEquals("<p>paragraph</p>".PHP_EOL, Antlers::parse($template, $this->variables));
    }

    public function testConditionsWithModifiers()
    {
        $template = "{{ if string|upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals("yes", Antlers::parse($template, $this->variables));
    }

    public function testConditionsWithRelaxedModifiers()
    {
        $template = "{{ if string | upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals("yes", Antlers::parse($template, $this->variables));
    }

    public function testTagsWithCurliesInParamsGetsParsed()
    {
        // the variables are inside Test@index
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $template = "{{ test variable='{string}' }}";

        $this->assertEquals('Hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testDateConditionWithChainedRelaxedModifiersWithSpacesInArguments()
    {
        $template = '{{ if (date | modify_date:+3 years | format:Y) == "2015" }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($template, $this->variables));
    }

    public function testArrayModifiersGetParsed()
    {
        $template = '{{ simple limit="1" }}{{ value }}{{ /simple }}';

        $this->assertEquals('one', Antlers::parse($template, $this->variables));
    }

    public function testRecursiveChildren()
    {
        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = \Foo\Bar\Tags\RecursiveChildren::class;

        $template = '<ul>{{ recursive_children }}<li>{{ title }}{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One<ul><li>Two</li><li>Three<ul><li>Four</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, Antlers::parse($template, []));
    }

    public function testRecursiveChildrenWithScope()
    {
        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = \Foo\Bar\Tags\RecursiveChildren::class;

        $template = '<ul>{{ recursive_children scope="item" }}<li>{{ item:title }}{{ if item:children }}<ul>{{ *recursive item:children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One<ul><li>Two</li><li>Three<ul><li>Four</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, Antlers::parse($template, []));
    }

    public function testEmptyValuesAreNotOverriddenByPreviousIteration()
    {
        $variables = [
            'loop' => [
                [
                    'one' => '[1.1]',
                    'two' => '[1.2]',
                ],
                [
                    'one' => '[2.1]'
                ]
            ]
        ];

        $this->assertEquals(
            '[1.1][1.2][2.1]',
            Antlers::parse('{{ loop }}{{ one }}{{ two }}{{ /loop }}', $variables)
        );
    }

    public function testEmptyValuesAreNotOverriddenByPreviousIterationWithParsing()
    {
        // the variables are inside Test@some_parsing
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $variables = [
            'loop' => [
                [
                    'one' => '[1.1]',
                    'two' => '[1.2]',
                ],
                [
                    'one' => '[2.1]'
                ]
            ]
        ];

        $this->assertEquals(
            '[1.1][1.2][2.1]',
            Antlers::parse('{{ loop }}{{ one }}{{ test:some_parsing of="two" }}{{ two }}{{ /test:some_parsing }}{{ /loop }}', $variables)
        );
    }

    public function testNestedArraySyntax()
    {
        $variables = [
            'hello' => [
                'world' => [
                    ['baz' => 'one'],
                    ['baz' => 'two'],
                ],
                'id' => '12345'
            ]
        ];

        $this->assertEquals(
            '[one][two]',
            Antlers::parse('{{ hello:world }}[{{ baz }}]{{ /hello:world }}', $variables)
        );

        $this->assertEquals(
            '[one][two]',
            Antlers::parse('{{ hello:world scope="s" }}[{{ s:baz }}]{{ /hello:world }}', $variables)
        );
    }

    function testParsesPhpWhenEnabled()
    {
        $this->assertEquals(
            'Hello wilderness!',
            Antlers::parser()->allowPhp()->parse('{{ string }}<?php echo "!"; ?>', $this->variables, [])
        );

        $this->assertEquals(
            'Hello wilderness&lt;?php echo "!"; ?>',
            Antlers::parse('{{ string }}<?php echo "!"; ?>', $this->variables, [])
        );
    }

    /** @test */
    function it_doesnt_parse_noparse_tags_and_requires_extractions_to_be_reinjected()
    {
        $parser = Antlers::parser();

        $parsed = $parser->parse('{{ noparse }}{{ string }}{{ /noparse }} {{ string }}', $this->variables);

        $this->assertEquals('noparse_ac3458695912d204af897d3c67f93cbe Hello wilderness', $parsed);

        $this->assertEquals('{{ string }} Hello wilderness', $parser->injectNoparse($parsed));
    }

    /** @test */
    function it_accepts_an_arrayable_object()
    {
        $this->assertEquals(
            'Hello World',
            Antlers::parse('{{ string }}', new ArrayableObject(['string' => 'Hello World']))
        );
    }

    /** @test */
    function it_throws_exception_for_non_arrayable_data_object()
    {
        try {
            Antlers::parse('{{ string }}', new NonArrayableObject(['string' => 'Hello World']));
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Expecting array or object implementing Arrayable. Encountered [Tests\View\Antlers\NonArrayableObject]', $e->getMessage());
            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    function it_throws_exception_for_unsupported_data_value()
    {
        try {
            Antlers::parse('{{ string }}', 'string');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Expecting array or object implementing Arrayable. Encountered [string]', $e->getMessage());
            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    function it_gets_augmented_value()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                return 'augmented ' . $value;
            }
        };

        $value = new Value('expected', 'test', $fieldtype);

        $parsed = Antlers::parse('{{ test }}', ['test' => $value]);

        $this->assertEquals('augmented expected', $parsed);
    }

    /** @test */
    function it_expands_augmented_value_when_used_as_an_array()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($values)
            {
                return collect($values)->map(function ($value) {
                    return strtoupper($value);
                })->all();
            }
        };

        $value = new Value([
            'one' => 'hello',
            'two' => 'world',
        ], 'test', $fieldtype);

        $parsed = Antlers::parse('{{ test }}{{ one }} {{ two }}{{ /test }}', ['test' => $value]);

        $this->assertEquals('HELLO WORLD', $parsed);
    }

    /** @test */
    function it_loops_over_value_object()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($values)
            {
                return collect($values)->map(function ($value) {
                    return collect($value)->map(function ($v) {
                        return strtoupper($v);
                    });
                })->toArray();
            }
        };

        $value = new Value([
            ['one' => 'uno', 'two' => 'dos'],
            ['one' => 'une', 'two' => 'deux'],
        ], 'test', $fieldtype);

        $parsed = Antlers::parse('{{ test }}{{ one }} {{ two }} {{ /test }}', ['test' => $value]);

        $this->assertEquals('UNO DOS UNE DEUX ', $parsed);
    }

    /** @test */
    function it_gets_nested_values_from_value_objects()
    {
        $value = new Value(['foo' => 'bar'], 'test');

        $parsed = Antlers::parse('{{ test:foo }}', ['test' => $value]);

        $this->assertEquals('bar', $parsed);
    }

    /** @test */
    function it_gets_nested_values_from_nested_value_objects()
    {
        $value = new Value(['foo' => 'bar'], 'test');

        $parsed = Antlers::parse('{{ nested:test:foo }}', [
            'nested' => [
                'test' => $value
            ]
        ]);

        $this->assertEquals('bar', $parsed);
    }

    /** @test */
    function it_gets_nested_values_from_within_nested_value_objects()
    {
        $value = new Value([
            'foo' => ['nested' => 'bar']
        ], 'test');

        $parsed = Antlers::parse('{{ nested:test:foo:nested }}', [
            'nested' => [
                'test' => $value
            ]
        ]);

        $this->assertEquals('bar', $parsed);
    }

    /** @test */
    function it_casts_objects_to_string_when_using_single_tags()
    {
        $object = new class {
            function __toString() {
                return 'string';
            }
        };

        $this->assertEquals(
            'string',
            Antlers::parse('{{ object }}', compact('object'))
        );
    }

    /** @test */
    function it_doesnt_output_anything_if_object_cannot_be_cast_to_a_string()
    {
        $object = new class {};

        $this->assertEquals('', Antlers::parse('{{ object }}', compact('object')));

        // TODO: Assert about log message
    }

    /** @test */
    function it_casts_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        $arrayableObject = new ArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $nonArrayableObject = new NonArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            'foo bar',
            Antlers::parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $arrayableObject
            ])
        );

        $this->assertEquals(
            '',
            Antlers::parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $nonArrayableObject
            ])
        );
        // TODO: Assert about log message
    }
}

class NonArrayableObject
{
    function __construct($data)
    {
        $this->data = $data;
    }
}

class ArrayableObject extends NonArrayableObject implements Arrayable
{
    function toArray() {
        return $this->data;
    }
}
