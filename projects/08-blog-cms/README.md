# Blog CMS

Lightweight content management system for blogging.

## Features

- Create, edit, publish posts
- Draft and published states
- Categories and tags
- SEO-friendly slugs
- Search
- Pagination
- Automatic publish timestamps

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use Blog\Database;
use Blog\BlogManager;

$db = new Database('blog.db');
$blog = new BlogManager($db);

// Create draft
$id = $blog->createPost(
    'My First Post',
    'This is the content...',
    'draft',
    'Technology',
    ['php', 'web']
);

// Publish
$blog->update($id, ['status' => 'published']);

// Get published posts
$posts = $blog->getPosts('published', null, 1, 10);

// Get by slug
$post = $blog->getBySlug('my-first-post');

// Search
$results = $blog->search('php');

// Delete
$blog->delete($id);
```

## Security

- Output escaping required in templates (htmlspecialchars)
- CSRF protection needed for forms
- Authentication needed for admin
- Server-side authorization checks required

## Limitations

- No user management
- No comments
- No media library
- No revision history
- No WYSIWYG editor
- No RSS feed
- Hidden navigation != authorization

## Production Requirements

- Add authentication
- Implement CSRF protection
- Add output escaping in views
- Implement authorization checks
- Add rate limiting
