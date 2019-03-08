<?php

namespace Tests\View\Antlers;

use Tests\TestCase;
use Statamic\API\Antlers;
use Statamic\Fields\Value;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Blueprint;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Data\Augmentable;
use Illuminate\Contracts\Support\Arrayable;
use Facades\Statamic\Fields\FieldtypeRepository;
use Statamic\Data\Augmentable as AugmentableTrait;

class ParserTest extends TestCase
{
    private $variables;

    public function setUp(): void
    {
        parent::setUp();

        $this->variables = [
            'string' => 'Hello wilderness',
            'simple' => ['one', 'two', 'three'],
            'complex' => [
                ['string' => 'the first string'],
                ['string' => 'the second string']
            ],
            'associative' => [
                'one' => 'hello',
                'two' => 'wilderness',
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
        $template = <<<EOT
before
{{ simple }}
    {{ value }}, {{ key or "0" }}, {{ index or "0" }}, {{ zero_index or "0" }}, {{ total_results }}
    {{ if first }}first{{ elseif last }}last{{ else }}neither{{ /if }}


{{ /simple }}
after
EOT;

$expected = <<<EOT
before
    one, 0, 1, 0, 3
    first

    two, 1, 2, 1, 3
    neither

    three, 2, 3, 2, 3
    last


after
EOT;

        $this->assertEquals($expected, Antlers::parse($template, $this->variables));
    }

    public function testComplexArrayVariable()
    {
        $template = <<<EOT
before
{{ complex }}
    {{ string }}, {{ key or "0" }}, {{ index or "0" }}, {{ zero_index or "0" }}, {{ total_results }}
    {{ if first }}first{{ elseif last }}last{{ else }}neither{{ /if }}


{{ /complex }}
after
EOT;

$expected = <<<EOT
before
    the first string, 0, 1, 0, 2
    first

    the second string, 1, 2, 1, 2
    last


after
EOT;

        $this->assertEquals($expected, Antlers::parse($template, $this->variables));
    }

    public function testAssociativeArrayVariable()
    {
        $template = <<<EOT
before
{{ associative }}
    {{ one }}
    {{ two }}
    {{ value or "no value" }}
    {{ key or "no key" }}
    {{ index or "no index" }}
    {{ zero_index or "no zero_index" }}
    {{ total_results or "no total_results" }}
    {{ first or "no first" }}
    {{ last or "no last" }}
{{ /associative }}
after
EOT;

$expected = <<<EOT
before
    hello
    wilderness
    no value
    no key
    no index
    no zero_index
    no total_results
    no first
    no last

after
EOT;

        $this->assertEquals($expected, Antlers::parse($template, $this->variables));
    }

    public function testNonExistantVariablesShouldBeNull()
    {
        $template = "{{ missing }}";

        $this->assertEquals('', Antlers::parse($template, $this->variables));
    }

    /** @test */
    function non_arrays_cannot_be_looped()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ string }}');

        $template = "{{ string }} {{ /string }}";

        $this->assertEquals('', Antlers::parse($template, $this->variables));
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
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an array variable as a string: {{ simple }}');

        $template = "{{ simple }}";

        $this->assertEquals(null, Antlers::parse($template, $this->variables));
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
            Antlers::parse('{{ loop }}{{ one }}{{ test:some_parsing var="two" }}{{ two }}{{ /test:some_parsing }}{{ /loop }}', $variables)
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
    function it_doesnt_parse_data_in_noparse_modifiers_and_requires_extractions_to_be_reinjected()
    {
        $parser = Antlers::parser();

        $variables = [
            'string' => 'hello',
            'content' => 'before {{ string }} after',
        ];

        $parsed = $parser->parse('{{ content | noparse }} {{ string }}', $variables);

        $this->assertEquals('noparse_6d6accbda6a2c1f2e7dd3932dcc70012 hello', $parsed);

        $this->assertEquals('before {{ string }} after hello', $parser->injectNoparse($parsed));
    }

    /** @test */
    function it_doesnt_parse_noparse_tags_inside_callbacks_and_requires_extractions_to_be_reinjected()
    {
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $parser = Antlers::parser();

$template = <<<EOT
{{ test:some_parsing }}{{ noparse }}{{ string }}{{ /noparse }}{{ /test:some_parsing }}
{{ test:some_loop_parsing }}
    {{ index }} {{ noparse }}{{ string }}{{ /noparse }} {{ string }}
{{ /test:some_loop_parsing }}
EOT;

$expectedBeforeInjection = <<<EOT
noparse_ac3458695912d204af897d3c67f93cbe
    1 noparse_ac3458695912d204af897d3c67f93cbe Hello wilderness
    2 noparse_ac3458695912d204af897d3c67f93cbe Hello wilderness

EOT;

$expectedAfterInjection = <<<EOT
{{ string }}
    1 {{ string }} Hello wilderness
    2 {{ string }} Hello wilderness

EOT;

        $parsed = $parser->parse($template, $this->variables);
        $this->assertEquals($expectedBeforeInjection, $parsed);
        $this->assertEquals($expectedAfterInjection, $parser->injectNoparse($parsed));
    }

    /** @test */
    function it_doesnt_parse_data_in_noparse_modifiers_inside_callbacks_and_requires_extractions_to_be_reinjected()
    {
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $parser = Antlers::parser();

        $variables = [
            'string' => 'hello',
            'content_for_single_tag' => 'beforesingle {{ string }} aftersingle',
            'content_for_tag_pair' => 'beforepair {{ string }} afterpair',
        ];

$template = <<<EOT
{{ test:some_parsing }}{{ content_for_single_tag | noparse }}{{ /test:some_parsing }}
{{ test:some_loop_parsing }}
    {{ index }} {{ content_for_tag_pair | noparse }} {{ string }}
{{ /test:some_loop_parsing }}
EOT;

$expectedBeforeInjection = <<<EOT
noparse_0548be789865a16ab6e495f84a3080c0
    1 noparse_aa4a7fa8e2faf61751b68038fee92c4d hello
    2 noparse_aa4a7fa8e2faf61751b68038fee92c4d hello

EOT;

$expectedAfterInjection = <<<EOT
beforesingle {{ string }} aftersingle
    1 beforepair {{ string }} afterpair hello
    2 beforepair {{ string }} afterpair hello

EOT;

        $parsed = $parser->parse($template, $variables);
        $this->assertEquals($expectedBeforeInjection, $parsed);
        $this->assertEquals($expectedAfterInjection, $parser->injectNoparse($parsed));
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
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an object variable as a string: {{ object }}');

        $object = new class {};

        $this->assertEquals('', Antlers::parse('{{ object }}', compact('object')));
    }

    /** @test */
    function it_casts_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        $arrayableObject = new ArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            'foo bar',
            Antlers::parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $arrayableObject
            ])
        );
    }

    /** @test */
    function it_cannot_cast_non_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ object }}');

        $nonArrayableObject = new NonArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            '',
            Antlers::parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $nonArrayableObject
            ])
        );
    }

    /** @test */
    function callback_tags_that_return_unparsed_simple_arrays_get_parsed()
    {
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $template = <<<EOT
{{ string }}
{{ test:return_simple_array }}
    {{ one }} {{ two }} {{ string }}
{{ /test:return_simple_array }}
EOT;

        $expected = <<<EOT
Hello wilderness
    a b Hello wilderness

EOT;

        $this->assertEquals($expected, Antlers::parse($template, $this->variables));
    }

    /** @test */
    function callback_tags_that_return_unparsed_multidimensional_arrays_get_parsed()
    {
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $template = <<<EOT
{{ string }}
{{ test:return_multidimensional_array }}
    {{ index }} {{ if first }}first{{ else }}not-first{{ /if }} {{ if last }}last{{ else }}not-last{{ /if }} {{ one }} {{ two }} {{ string }}
{{ /test:return_multidimensional_array }}
EOT;

        $expected = <<<EOT
Hello wilderness
    1 first not-last a b Hello wilderness
    2 not-first last c d Hello wilderness

EOT;

        $this->assertEquals($expected, Antlers::parse($template, $this->variables));
    }

    /** @test */
    function callback_tags_that_return_empty_arrays_get_parsed_with_no_results()
    {
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $template = <<<EOT
{{ test:return_empty_array }}
    {{ if no_results }}no results{{ else }}there are results{{ /if }}
{{ /test:return_empty_array }}
EOT;

        $expected = <<<EOT
    no results
EOT;

        $this->assertEquals($expected, Antlers::parse($template, $this->variables));
    }

    /** @test */
    function it_automatically_augments_when_using_tag_pairs()
    {
        $augmentable = new AugmentableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            'FOO! bar',
            Antlers::parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $augmentable
            ])
        );
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

class AugmentableObject extends ArrayableObject implements Augmentable
{
    use AugmentableTrait;

    public function blueprint()
    {
        FieldtypeRepository::shouldReceive('find')->andReturn(new class extends Fieldtype {
            public function augment($data)
            {
                return strtoupper($data) . '!';
            }
        });

        return (new Blueprint)->setContents(['fields' => [
            ['handle' => 'one', 'field' => ['type' => 'test']]
        ]]);
    }
}
