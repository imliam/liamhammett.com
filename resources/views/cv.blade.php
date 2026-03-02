<x-page title="CV">
    <x-container class="mt-16" style="view-transition-name:main">
        <div class="px-6 lg:px-8">
            <div class="relative max-w-3xl print:max-w-full mx-auto space-y-8 text-base leading-7 text-gray-700">
                <div class="prose print:text-sm">

                    <div class="not-prose mb-8">
                        <h1 class="font-title text-7xl sm:text-8xl print:text-4xl tracking-tight text-slate-950 mb-2 print:mb-1 print:hidden">Liam Hammett</h1>
                        <p class="text-sm text-gray-400 flex flex-wrap gap-x-1">
                            <a href="mailto:liam@liamhammett.com" class="hover:text-gray-600 transition-colors">liam@liamhammett.com</a> &middot;
                            <a href="https://www.linkedin.com/in/liam-hammett/" class="hover:text-gray-600 transition-colors">LinkedIn</a> &middot;
                            <a href="https://www.github.com/imliam" class="hover:text-gray-600 transition-colors">GitHub</a> &middot;
                            <a href="https://liamhammett.com/" class="hover:text-gray-600 transition-colors">Blog</a> &middot;
                            <a href="https://twitter.com/LiamHammett" class="hover:text-gray-600 transition-colors">Twitter</a> &middot;
                            <a href="https://www.youtube.com/@imliamhammett" class="hover:text-gray-600 transition-colors">YouTube</a>
                        </p>
                    </div>

                    <p>A UK-based Senior Tech Lead and Software Engineer who equally loves leading a team and building amazing software, with over a decade of experience building intuitive, scalable, and user-centric digital products with <mark>Laravel and PHP</mark>, driven by&hellip;</p>

                    <ul>
                        <li><strong>the product</strong> - shaping technical direction to deliver real business value</li>
                        <li><strong>the user experience</strong> - building intuitive tools that solve real user needs</li>
                        <li><strong>the developer experience</strong> - growing teams and codebases for long-term velocity</li>
                        <li><strong>knowledge sharing</strong> - speaking at conferences, contributing to open-source and writing to help others</li>
                    </ul>

                    <x-divider class="my-8" />

                    <h2 class="print:mt-8">Skills &amp; Technologies</h2>

                    <div class="not-prose space-y-3 print:space-y-1">
                        @php
                            $skillGroups = [
                                'Languages' => ['PHP', 'TypeScript', 'JavaScript', 'Golang', 'SQL', 'Lua', 'Python'],
                                'Frameworks' => ['Laravel', 'Filament', 'Symfony', 'React', 'Vue.js', 'Alpine.js', 'Tailwind CSS'],
                                'Infrastructure' => ['Docker', 'Kubernetes', 'AWS', 'Varnish', 'GitHub Actions', 'GitLab CI/CD'],
                                'Data' => ['MySQL', 'Postgres', 'MongoDB', 'Solr', 'Redis'],
                                'Observability' => ['OpenTelemetry', 'Nightwatch', 'Prometheus', 'Jaeger', 'Grafana', 'Incident.io'],
                                'AI & Tooling' => ['Agentic Workflows', 'LLM integrations', 'RAG pipelines'],
                                'Practices' => ['Team leadership', 'On-call & incident management', 'CI/CD', 'Agile', 'Mentoring'],
                            ];
                        @endphp
                        @foreach ($skillGroups as $label => $skills)
                            <div class="flex flex-wrap items-baseline gap-2 print:gap-1">
                                <span class="text-xs font-semibold w-28 text-gray-500 uppercase tracking-wider shrink-0">{{ $label }}</span>
                                @foreach ($skills as $skill)
                                    <span class="inline-block px-2.5 py-0.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-full">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <x-divider class="my-8" />

                    <h2 class="print:mt-8 break-before-page">Work History</h2>

                    <div class="border-l-2 border-gray-200 pl-6 ml-2 space-y-10 print:space-y-6" style="print-color-adjust: exact; -webkit-print-color-adjust: exact;">

                        {{-- Future Publishing --}}
                        <div class="relative">
                            <div class="absolute -left-[2.05rem] print:-left-8 top-14 print:size-3 print:top-11 size-4 rounded-full bg-gray-400 border-4 border-white print:border-2 print:border-gray-400 print:bg-gray-400 outline-2 outline outline-gray-200" style="print-color-adjust: exact; -webkit-print-color-adjust: exact;"></div>
                            <div class="flex items-center gap-4 mb-2">
                                <img class="no-shadow w-32 print:w-24 shrink-0" src="{{ asset('/images/cv/future-plc.webp') }}" />
                                <p class="text-gray-400 mt-0 mb-0 text-right flex-1">
                                    <strong>Senior Tech Lead</strong> (<em>2026 &mdash; Present</em>)
                                </p>
                            </div>
                            <p>Developing and maintaining a multi-tenant publishing platform serving <strong>80+ brands</strong> at <strong>8,000+ requests per second</strong>, maintaining a <strong>99.99% uptime SLI</strong>.</p>
                            <ul>
                                <li>Line-managing a <strong>team of 8 engineers</strong>, mentoring developers towards senior roles and growing multiple into leads running their own teams</li>
                                <li>Owning and architecting a variety of distributed applications built with PHP, Golang and TypeScript served from <strong>Kubernetes</strong> with Varnish/Fastly CDN layers, multiple database technologies and AWS services, with extensive observability to detect and resolve issues before user impact</li>
                                <li>Reducing CI/CD pipeline time from <strong>25+ minutes to under 6 minutes</strong>, accelerating deployment velocity for the wider engineering team</li>
                                <li>Leading performance optimisations that <strong>halve backend CPU time</strong>, improving cache hit rates through robust caching strategies, monitored via profilers and CWV dashboards</li>
                                <li>Leading <strong>internationalisation</strong> of the platform to support <strong>12 languages across 20+ regions</strong> for multiple high-profile brands</li>
                                <li>Leading development of an <strong>LLM-powered translation pipeline</strong> that reduces editorial translation time by ~50%, saving 1h+ per article while conforming to brand style guides</li>
                                <li>Introducing and upskilling the team on <strong>agentic AI coding tools</strong> (Claude Code, GitHub Copilot, Cursor, etc.) to improve development velocity</li>
                                <li>Improving developer experience for engineering teams of 30+, driving <strong>component-based architecture</strong> adoption to reduce conflicts and accelerate feature delivery</li>
                                <li>Collaborating with audience-development teams to improve SEO, readership and retention; organising internal talks, conferences and hack days to drive growth and innovation</li>
                            </ul>
                        </div>

                        {{-- Helm Squared --}}
                        <div class="relative">
                            <div class="absolute -left-[2.05rem] print:-left-8 top-12 print:size-3 print:top-10 size-4 rounded-full bg-gray-400 border-4 border-white print:border-2 print:border-gray-400 print:bg-gray-400 outline-2 outline outline-gray-200" style="print-color-adjust: exact; -webkit-print-color-adjust: exact;"></div>
                            <div class="flex items-center gap-4 mb-2">
                                <img class="no-shadow w-32 print:w-24 shrink-0" src="{{ asset('/images/cv/helm-squared.png') }}" />
                                <p class="text-gray-400 mt-0 mb-0 text-right flex-1"><strong>Full Stack Developer</strong> (<em>2018 &mdash; 2019</em>)</p>
                            </div>
                            <p>Building a SaaS ticketing platform used by <strong>hundreds of event organisers</strong> to manage thousands of events.</p>
                            <ul>
                                <li>Building <strong>multicurrency payment integration</strong> (GBP, USD, EUR) with Stripe Connect in a whitelabelled, user-friendly flow</li>
                                <li>Introducing Docker for local and staging environments and implementing CD pipelines to deploy Laravel and Yii applications to AWS</li>
                                <li>Overhauling legacy backend and frontend, improving runtime performance, accelerating feature development and easing onboarding for new developers</li>
                                <li>Internationalising the flagship application to expand into new markets, widening sales reach</li>
                                <li>Adding <strong>multi-tenant functionality</strong> to give organisers more control over their branding and configuration</li>
                                <li>Developing <strong>React Native mobile app</strong> for on-the-door ticket management and QR code scanning</li>
                            </ul>
                        </div>

                        {{-- Microtest --}}
                        <div class="relative break-before-page">
                            <div class="absolute -left-[2.05rem] print:-left-8 top-8 print:size-3 print:top-8 size-4 rounded-full bg-gray-400 border-4 border-white print:border-2 print:border-gray-400 print:bg-gray-400 outline-2 outline outline-gray-200" style="print-color-adjust: exact; -webkit-print-color-adjust: exact;"></div>
                            <div class="flex items-center gap-4 mb-2">
                                <img class="no-shadow w-32 print:w-24 shrink-0" src="{{ asset('/images/cv/microtest.png') }}" />
                                <p class="text-gray-400 mt-0 mb-0 text-right flex-1"><strong>Software Engineer</strong> (<em>2015 &mdash; 2018</em>)</p>
                            </div>
                            <p>Creating web-based clinical software for the <strong>NHS</strong>, used by <strong>75+ GP practices and hospitals</strong>.</p>
                            <ul>
                                <li>Leading development of a <strong>care planning product</strong> with a team of 5 engineers, coordinating SOAP API development in Java with first-party integrations in Laravel</li>
                                <li>Building a healthcare application used across the country to assist medical professionals with <strong>rapid diagnoses</strong> and identifying drug trial candidates</li>
                                <li>Developing real-time REST APIs and front-end modules for a modern clinical application with <strong>WebSocket-driven interactivity</strong></li>
                                <li>Maintaining and rewriting legacy web applications in a mix of PHP, Java, Python, C# and C++ to improve reliability and developer productivity</li>
                                <li>Conducting user research at user groups and providing direct technical support to healthcare customers</li>
                                <li>Building automated <strong>end-to-end test suites</strong> for web and desktop applications using Selenium, Qt and other frameworks</li>
                            </ul>
                        </div>

                    </div>

                    <x-divider class="my-8" />

                    <h2 class="print:mt-8">Other</h2>
                    <ul>
                        <li><strong>Conference speaker</strong> at <a href="https://www.youtube.com/watch?v=K5nk15XOMTI">Laracon EU</a>, <a href="https://www.youtube.com/watch?v=o6dIlejWNsI">PHP UK</a> and <a href="https://phpconference.nl/schedule-2026/">Dutch PHP Conference</a></li>
                        <li><strong>Open source</strong> projects with 4,000+ GitHub stars and 1M+ downloads, including <a href="https://cpx.dev">CLI tools</a>, <a href="https://marketplace.visualstudio.com/publishers/liamhammett">IDE extensions</a> and <a href="https://github.com/imliam?tab=repositories&sort=stargazers&type=source">many packages</a> to improve developer experiences</li>
                        <li><strong>Interests:</strong> Learning German, D&amp;D, rock, punk &amp; metal music, board &amp; video gaming</li>
                    </ul>

                    <p class="text-gray-400 text-xs">References and further information is available upon request.</p>
                </div>
            </div>
        </div>
    </x-container>
</x-page>
