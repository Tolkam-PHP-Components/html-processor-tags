# tolkam/html-processor-tags

HTML processor middleware for easier HTML tags manipulation.

## Documentation

The code is rather self-explanatory and API is intended to be as simple as possible. Please, read the sources/Docblock if you have any questions. See [Usage](#usage) for quick start.

## Usage

````php
use Tolkam\HTMLProcessor\Context;
use Tolkam\HTMLProcessor\HTMLProcessor;
use Tolkam\HTMLProcessor\Tags\Handler\TagsHandlerInterface;
use Tolkam\HTMLProcessor\Tags\NodesFactory;
use Tolkam\HTMLProcessor\Tags\Tag;
use Tolkam\HTMLProcessor\Tags\TagsMiddleware;

$processor = new HTMLProcessor;

$tagsMiddleware = new TagsMiddleware;

$tagsMiddleware->addHandler('my-custom-tag', new class implements TagsHandlerInterface {
    
    /**
     * @param string       $tagName
     * @param Tag[]        $tags
     * @param Context      $context
     * @param NodesFactory $factory
     */
    public function handle(
        string $tagName,
        array $tags,
        Context $context,
        NodesFactory $factory
    ): void {
        foreach ($tags as $tag) {
            $body = $tag->getBody() . ', tag context: ' . $context->get('name');
            
            $tag->replaceWith(
                $factory->element('p', $body)
            );
        }
    }
});

$processor->addMiddleware($tagsMiddleware);

$input = '<div><my-custom-tag>My text</my-custom-tag></div>';
$context = new Context(['name' => 'My Context']);

echo $processor->process($input, $context);
````

Input: `<div><my-custom-tag>My text</my-custom-tag></div>`

Output: `<div><p>My text, tag context: My Context</p></div>`.

## License

Proprietary / Unlicensed 🤷
