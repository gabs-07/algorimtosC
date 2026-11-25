@props(['texto'])

<div {{ $attributes->merge(['class' => 'space-y-3 text-gray-800 leading-relaxed [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:text-lg [&_h3]:font-semibold [&_ul]:list-disc [&_ul]:ps-6 [&_ol]:list-decimal [&_ol]:ps-6 [&_pre]:bg-gray-900 [&_pre]:text-gray-100 [&_pre]:p-4 [&_pre]:rounded-md [&_pre]:overflow-x-auto [&_pre]:text-sm [&_code]:font-mono [&_p>code]:bg-gray-100 [&_p>code]:px-1 [&_p>code]:rounded']) }}>
    {!! \Illuminate\Support\Str::markdown((string) $texto, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
</div>
