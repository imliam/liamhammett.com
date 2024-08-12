<x-page title="404">

    <div class="min-h-[50vh] text-center flex flex-col items-center justify-center gap-16 font-mono">
        <h1 class="font-extrabold uppercase leading-1 text-8xl text-pretty max-w-96 mx-auto text-shadow-sq shadow-orange-500">
            404
        </h1>

        <p class="uppercase font-semibold text-2xl tracking-wide leading-none text-gray-700">
            Looks like you've found... nothing
        </p>

        <a href="{{ url('/') }}" class="text-orange-50 bg-orange-700 hover:bg-orange-500 hover:text-orange-100 rounded-2xl text-2xl px-6 py-4">Go home</a>
    </div>
</x-page>
