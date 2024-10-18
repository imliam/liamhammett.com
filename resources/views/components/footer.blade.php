<x-container class="my-16 text-sm text-center space-y-8 px-8">
    <div>
        Hit me up on
        <a href="https://twitter.com/LiamHammett" class="relative group font-semibold text-orange-700 hover:text-orange-500">
            <span class="inline-block group-hover:hidden">Twitter</span>
            <span class="hidden group-hover:inline-block"><s>Twitter</s></span>
            <span class="hidden group-hover:inline-block absolute text-center bg-white text-red-300 p-2 rounded shadow-lg top-4 -left-20 -right-20 text-xs">I guess it's called <span class="text-red-500 font-bold">X</span> now</span>
        </a>
        /
        <a href="https://github.com/imliam" class="font-semibold text-orange-700 hover:text-orange-500">GitHub</a>
        /
        <a href="https://www.linkedin.com/in/liam-hammett/" class="relative group font-semibold text-orange-700 hover:text-orange-500">
            LinkedIn
            <span class="hidden group-hover:inline-block absolute text-center bg-white text-red-300 p-2 rounded shadow-lg top-4 -left-24 -right-24 text-xs">If you're a corporate kind of person</span>
        </a>
        /
        <a href="mailto:liam@liamhammett.com" class="font-semibold text-orange-700 hover:text-orange-500">email me</a>
    </div>
    <div class="text-xs uppercase text-gray-500 font-semibold tracking-wide">Copyright &copy; {{ now()->year }} Liam Hammett <span class="text-gray-400">and all that kind of stuff</span></div>
</x-container>
