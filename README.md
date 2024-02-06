# Be API Blocks Theme

## Blocks blacklist
Choose which blocks to exclude from the editor.

In ``inc/Services/Editor.php``

```php
$excluded = [
    'core/more',
];
```

## Images

Register custom images sizes and display them in the editor. 

In ``inc/Services/Theme.php``

```php
 add_image_size( 'large-square', 1024, 1024, true );
```
and then make them available in the editor.

In ``inc/Services/Editor.php``
```php
function gutenberg_images_sizes( $sizes ) : array {
    return array_merge(
        $sizes,
        [
            'large-square'   =>  __( 'Large square', 'beapi-blocks-theme' ),
        ]
    );
}
```


