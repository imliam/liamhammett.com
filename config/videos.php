<?php

return collect([
    ['id' => 'p6PME-HsNxE', 'title' => 'PHPShell - PHP in the Terminal?'],
    ['id' => 'oleCU7EIIF0', 'title' => 'Two Soups and Two Cookies | dump(🧠); // Brain Dump'],
    ['id' => '2MR5hOkHQ9Y', 'title' => 'Laravel Smarter Tips - Destructive Database Commands'],
    ['id' => '5qKQiknGeic', 'title' => 'Laravel Smarter Tips - Force HTTPS'],
    ['id' => 'sz27TMkE724', 'title' => 'Laravel Smarter Tips - Publish Stub Files'],
    ['id' => '8ykiu6xq_MA', 'title' => 'Laravel Smarter Tips - Sleep::fake()'],
])->map(fn ($video) => $video + [
    'url' => "https://www.youtube.com/watch?v={$video['id']}",
    'thumbnail' => "https://i.ytimg.com/vi/{$video['id']}/hqdefault.jpg",
])->all();
