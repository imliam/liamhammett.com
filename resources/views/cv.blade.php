<x-page title="CV">
    <x-container class="mt-16">
        <div class="px-6 lg:px-8">
            <div class="relative max-w-3xl mx-auto space-y-8 text-base leading-7 text-gray-700">
                <div class="prose">

                    <h1>Liam Hammett</h1>

                    <p>I'm a UK-based developer with a deep passion for building intuitive, scalable, and user-centric digital products. If I'm working on something, I'm going to put my energy into the things I care about&hellip;</p>

                    <ul>
                        <li><strong>the product</strong> - to have input in the decision making process</li>
                        <li><strong>the user experience</strong> - to build an intuitive tool that takes the user's real needs into account</li>
                        <li><strong>the developer experience</strong> - to be able to maintain and grow the code indefinitely</li>
                        <li><strong>sharing</strong> - to contribute to the open-source ecosystem, give talks and write articles to help others</li>
                    </ul>

                    <x-divider />

                    <h2>Work</h2>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-8">
                        <div class=""><img src="{{ asset('/images/cv/future-plc.png') }}" /></div>
                        <div class="col-span-2">
                            <h3>Future Publishing</h3>
                            <p class="text-gray-500"><strong>Tech Lead</strong> (<em>2019 &mdash; Present</em>)</p>
                            <p>Developing and maintaining a platform for a top publisher serving millions of pages per hour.</p>
                            <ul>
                                <li>Leading and line-managing 10+ engineers, mentoring and growing them towards more senior roles</li>
                                <li>Bespoke distributed stack involving PHP, Golang and TypeScript. Use of various database technologies such as MySQL, MongoDB, Solr, ArangoDB and Riak. Served from Kubernetes, with Varnish and Fastly as a caching layer</li>
                                <li>Use of a wide array of telemetry and tooling (such as Sentry, Prometheus, Jaeger, Kibana, KEDA, Incident.io and more) to ensure uptime SLAs are met and any issues get resolved promptly</li>
                                <li>Building and using complex CI/CD processes to orchestrate deploys across multiple services</li>
                                <li>Extensive work with the audience-development team to improve SEO, readership and retention</li>
                                <li>Leading internationalisation across multiple high-profile brands and applications to bring them to market in various new regions</li>
                                <li>Refacting major flagship legacy application over multiple years to increase performance multiple times and improve development experience and environment for large development teams, enabling them to do more faster</li>
                                <li>Driving the team towards a component-based application structure to help the larger team move quicker without conflicting as much</li>
                                <li>Maintaining and updating frontend code using Sass/PostCSS and React/VueJS for different projects, steering towards TailwindCSS and Alpine.js</li>
                            </ul>
                        </div>
                    </div>

                    <x-divider />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-8">
                        <div class=""><img class="no-shadow" src="{{ asset('/images/cv/helm-squared.png') }}" /></div>
                        <div class="col-span-2">
                            <h3>Helm Squared Ltd.</h3>
                            <p class="text-gray-500"><strong>Full Stack Developer </strong> (<em>2018 &mdash; 2019</em>)</p>
                            <p>Building a SaaS ticketing platform.</p>
                            <ul>
                                <li>Working with Yii 2 framework, VueJS, and introducing Docker for local and staging environments, using CD to deploy production applications to AWS</li>
                                <li>Developing deep in-app integration to support multicurrency payments through Stripe Connect in a whitelabelled, user-friendly manner</li>
                                <li>Overhauling and refactoring both legacy backend and frontend to allow quicker feature development, improve runtime performance and to help onboard new developers</li>
                                <li>Internationalisation and localisation of flagship application to work in more markets, leading to wider sales reach</li>
                                <li>Bringing multi-tenant functionality to flagship app to give more control to customers</li>
                                <li>Working on React Native mobile app for ticket management and QR scanning</li>
                            </ul>
                        </div>
                    </div>

                    <x-divider />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-8">
                        <div class=""><img class="no-shadow" src="{{ asset('/images/cv/microtest.png') }}" /></div>
                        <div class="col-span-2">
                            <h3>Microtest Ltd.</h3>
                            <p class="text-gray-500"><strong>Software Engineer &amp; Web Tester</strong> (<em>2015 &mdash; 2018</em>)</p>
                            <p>Creating web-based clinical software for the NHS.</p>
                            <ul>
                                <li>Team lead for a flagship care planning product, co-ordinating SOAP API development with Java and first-party-integrations of it in PHP (Laravel) with a team of 5</li>
                                <li>Developing unique healthcare application used in hospitals across the country to assist medical professionals giving quick diagnoses and determining possible drug trial candidates</li>
                                <li>Enhancing, maintaining, and rewriting a multitude of legacy web-based applications</li>
                                <li>Developing REST APIs and front-end modules for brand new modern clinical application with big focus on real-time user interactivity, web-sockets and heavy traffic</li>
                                <li>Programming and maintaining miscellaneous projects and utilities in a variety of languages, primarily PHP but including Java, Python, C# and C++</li>
                                <li>Talking directly with customers to offer technical support on in-depth products and conducting frequent user research at user groups</li>
                                <li>Automating end-to-end tests for multiple web and desktop applications with Selenium, Qt and other frameworks</li>
                            </ul>
                        </div>
                    </div>

                    <x-divider />

                    <h3>What else have you worked on?</h3>
                    <ul>
                        <li><strong>Atelier 801 SARL</strong> <em class="text-gray-500">(2012&mdash;2021, volunteering)</em> - Building multilingual tools for an online game with 100 million+ active users, such as a HTML5 canvas-based map editor, scraping utilities, secure databases to process applications, and minigames written in Lua to drive interaction</li>
                        <li><strong>CombatFPS</strong> <em class="text-gray-500">(2008&mdash;2012)</em> - Founding/running a top FPS gaming forum that got millions of posts, and developing a multitude of web tools for it such as clan management and image generators in PHP</li>
                        <li><strong>Kongonia</strong> <em class="text-gray-500">(2007&mdash;2011)</em> - Founding/running a flash game review and tutorial site, working with some game developers to get insight into the game dev process, and liasing with other writers for the site</li>
                    </ul>

                    <p>I share a lot of what I build. Check out what I've done or get in touch with me on the following platforms:</p>

                    <ul>
                        <li><a href="https://liamhammett.com/">Personal Blog</a></li>
                        <li><a href="https://twitter.com/LiamHammett">Twitter</a></li>
                        <li><a href="https://www.github.com/imliam">GitHub</a></li>
                        <li><a href="https://www.linkedin.com/in/liam-hammett-79691137/">LinkedIn</a></li>
                        <li><a href="https://www.youtube.com/@imliamhammett">YouTube</a></li>
                        <li><a href="mailto:liam@liamhammett.com">Email</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </x-container>
</x-page>
