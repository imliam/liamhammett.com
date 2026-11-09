<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">
<link rel="alternate" type="application/rss+xml" title="{{ $podcast->title }}" href="{{ $podcast->getFeedUrl() }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $artwork }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $artwork }}">
