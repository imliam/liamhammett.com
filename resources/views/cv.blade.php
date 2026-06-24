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

                        {{-- Geocodio --}}
                        <div class="relative">
                            <div class="absolute -left-[2.05rem] print:-left-8 top-14 print:size-3 print:top-11 size-4 rounded-full bg-gray-400 border-4 border-white print:border-2 print:border-gray-400 print:bg-gray-400 outline-2 outline outline-gray-200" style="print-color-adjust: exact; -webkit-print-color-adjust: exact;"></div>
                            <div class="flex items-center gap-4 mb-2">
                                <svg aria-hidden="true" class="no-shadow w-32 print:w-24 shrink-0" viewBox="0 0 813 253" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M86.572 186.097C87.6352 185.511 118.981 153.916 121.474 151.466L133.132 139.691L113.555 120.309L86.572 147.187C84.5388 145.507 82.6502 143.66 80.926 141.665C73.8035 133.96 69.7957 123.896 69.6766 113.416C69.5575 102.936 73.3354 92.7833 80.2809 84.9189C87.2265 77.0545 96.847 72.0359 107.285 70.8322C117.723 69.6285 128.237 72.325 136.799 78.4011C140.106 80.6931 143.026 83.4951 145.451 86.7022C151.517 94.4677 154.697 104.09 154.45 113.932C154.203 123.774 150.543 133.225 144.094 140.678C142.298 142.762 140.428 144.518 138.412 146.419C123.344 161.413 108.129 176.479 93.1711 191.545L112.712 211C114.435 209.83 163.305 160.681 165.505 158.121C197.254 121.077 181.306 61.5428 133.646 46.3667C123.144 43.0034 111.998 42.1447 101.104 43.8596C90.2094 45.5744 79.8701 49.8149 70.9174 56.2403C67.5378 58.7141 64.3513 61.4402 61.3853 64.3951C50.6826 75.6104 44.0013 90.0429 42.3832 105.442C40.765 120.842 44.301 136.343 52.4398 149.528C54.6983 153.077 57.223 156.451 59.9921 159.621L68.7177 168.434C73.4837 173.188 81.9892 182.074 86.462 186.133" fill="#E62B28"></path>
                                    <path d="M340.548 113.201C340.548 111.456 340.312 109.757 339.84 108.106C339.415 106.454 338.684 104.991 337.646 103.718C336.655 102.396 335.334 101.335 333.682 100.533C332.031 99.7305 330.026 99.3294 327.666 99.3294C323.514 99.3294 320.259 100.533 317.899 102.939C315.54 105.298 313.983 108.719 313.228 113.201H340.548ZM312.945 125.941C313.653 132.075 315.517 136.534 318.536 139.318C321.556 142.055 325.449 143.423 330.214 143.423C332.762 143.423 334.956 143.116 336.797 142.503C338.637 141.889 340.265 141.205 341.68 140.45C343.143 139.695 344.464 139.011 345.644 138.398C346.87 137.784 348.144 137.478 349.465 137.478C351.211 137.478 352.532 138.115 353.429 139.389L359.799 147.245C357.534 149.84 355.08 151.963 352.438 153.615C349.843 155.219 347.153 156.493 344.37 157.437C341.633 158.333 338.873 158.946 336.089 159.277C333.352 159.654 330.733 159.843 328.233 159.843C323.09 159.843 318.253 159.017 313.724 157.366C309.241 155.667 305.301 153.166 301.904 149.863C298.554 146.561 295.888 142.456 293.906 137.548C291.972 132.641 291.004 126.932 291.004 120.421C291.004 115.514 291.83 110.866 293.482 106.478C295.18 102.09 297.587 98.2442 300.701 94.9413C303.862 91.6384 307.66 89.0197 312.096 87.0851C316.578 85.1506 321.627 84.1833 327.242 84.1833C332.102 84.1833 336.537 84.9382 340.548 86.4481C344.605 87.958 348.074 90.1521 350.952 93.0303C353.877 95.9085 356.142 99.4473 357.746 103.647C359.398 107.799 360.223 112.517 360.223 117.802C360.223 119.453 360.153 120.798 360.011 121.836C359.87 122.874 359.61 123.7 359.233 124.313C358.855 124.927 358.336 125.351 357.675 125.587C357.062 125.823 356.26 125.941 355.269 125.941H312.945Z" fill="#E62B28"></path>
                                    <path d="M401.07 84.1833C406.638 84.1833 411.71 85.0562 416.287 86.802C420.864 88.5478 424.78 91.0486 428.036 94.3043C431.339 97.56 433.887 101.523 435.68 106.195C437.52 110.819 438.44 116.033 438.44 121.836C438.44 127.687 437.52 132.972 435.68 137.69C433.887 142.361 431.339 146.348 428.036 149.651C424.78 152.907 420.864 155.431 416.287 157.224C411.71 158.97 406.638 159.843 401.07 159.843C395.455 159.843 390.336 158.97 385.712 157.224C381.135 155.431 377.171 152.907 373.821 149.651C370.518 146.348 367.947 142.361 366.107 137.69C364.314 132.972 363.417 127.687 363.417 121.836C363.417 116.033 364.314 110.819 366.107 106.195C367.947 101.523 370.518 97.56 373.821 94.3043C377.171 91.0486 381.135 88.5478 385.712 86.802C390.336 85.0562 395.455 84.1833 401.07 84.1833ZM401.07 143.564C406.119 143.564 409.823 141.795 412.182 138.256C414.588 134.67 415.791 129.244 415.791 121.978C415.791 114.711 414.588 109.309 412.182 105.77C409.823 102.231 406.119 100.462 401.07 100.462C395.88 100.462 392.081 102.231 389.675 105.77C387.269 109.309 386.066 114.711 386.066 121.978C386.066 129.244 387.269 134.67 389.675 138.256C392.081 141.795 395.88 143.564 401.07 143.564Z" fill="#E62B28"></path>
                                    <path d="M499.457 102.727C498.796 103.529 498.159 104.166 497.546 104.638C496.932 105.109 496.059 105.345 494.927 105.345C493.842 105.345 492.851 105.086 491.954 104.567C491.105 104.048 490.138 103.482 489.052 102.868C487.967 102.208 486.693 101.618 485.231 101.099C483.768 100.58 481.951 100.32 479.781 100.32C477.091 100.32 474.756 100.816 472.774 101.807C470.839 102.797 469.235 104.213 467.961 106.053C466.687 107.893 465.744 110.158 465.13 112.848C464.517 115.49 464.21 118.486 464.21 121.836C464.21 128.867 465.555 134.269 468.244 138.044C470.981 141.819 474.732 143.706 479.498 143.706C482.046 143.706 484.051 143.399 485.514 142.786C487.024 142.125 488.298 141.418 489.336 140.663C490.374 139.86 491.317 139.129 492.167 138.469C493.063 137.808 494.172 137.478 495.493 137.478C497.239 137.478 498.56 138.115 499.457 139.389L505.826 147.245C503.562 149.84 501.179 151.963 498.678 153.615C496.177 155.219 493.606 156.493 490.963 157.437C488.368 158.333 485.75 158.946 483.107 159.277C480.465 159.654 477.893 159.843 475.393 159.843C470.91 159.843 466.616 158.994 462.511 157.295C458.454 155.596 454.868 153.143 451.753 149.934C448.686 146.679 446.233 142.692 444.393 137.973C442.6 133.255 441.703 127.876 441.703 121.836C441.703 116.552 442.482 111.621 444.039 107.044C445.643 102.42 447.979 98.4329 451.046 95.0828C454.16 91.6856 457.982 89.0197 462.511 87.0851C467.088 85.1506 472.373 84.1833 478.365 84.1833C484.122 84.1833 489.17 85.1034 493.511 86.9436C497.852 88.7838 501.769 91.4732 505.26 95.0121L499.457 102.727Z" fill="#E62B28"></path>
                                    <path d="M542.989 84.1833C548.557 84.1833 553.629 85.0562 558.206 86.802C562.783 88.5478 566.699 91.0486 569.955 94.3043C573.258 97.56 575.806 101.523 577.599 106.195C579.439 110.819 580.359 116.033 580.359 121.836C580.359 127.687 579.439 132.972 577.599 137.69C575.806 142.361 573.258 146.348 569.955 149.651C566.699 152.907 562.783 155.431 558.206 157.224C553.629 158.97 548.557 159.843 542.989 159.843C537.374 159.843 532.255 158.97 527.631 157.224C523.054 155.431 519.091 152.907 515.741 149.651C512.438 146.348 509.866 142.361 508.026 137.69C506.233 132.972 505.336 127.687 505.336 121.836C505.336 116.033 506.233 110.819 508.026 106.195C509.866 101.523 512.438 97.56 515.741 94.3043C519.091 91.0486 523.054 88.5478 527.631 86.802C532.255 85.0562 537.374 84.1833 542.989 84.1833ZM542.989 143.564C548.038 143.564 551.742 141.795 554.101 138.256C556.508 134.67 557.711 129.244 557.711 121.978C557.711 114.711 556.508 109.309 554.101 105.77C551.742 102.231 548.038 100.462 542.989 100.462C537.799 100.462 534.001 102.231 531.594 105.77C529.188 109.309 527.985 114.711 527.985 121.978C527.985 129.244 529.188 134.67 531.594 138.256C534.001 141.795 537.799 143.564 542.989 143.564Z" fill="#E62B28"></path>
                                    <path d="M631.963 104.991C630.311 103.151 628.518 101.877 626.584 101.17C624.649 100.415 622.62 100.037 620.497 100.037C618.468 100.037 616.604 100.438 614.906 101.24C613.207 102.042 611.721 103.34 610.447 105.133C609.22 106.879 608.253 109.167 607.545 111.998C606.837 114.829 606.483 118.25 606.483 122.261C606.483 126.13 606.766 129.386 607.333 132.028C607.899 134.67 608.677 136.817 609.668 138.469C610.706 140.073 611.933 141.229 613.348 141.937C614.764 142.644 616.345 142.998 618.09 142.998C619.695 142.998 621.134 142.857 622.408 142.574C623.729 142.243 624.932 141.795 626.017 141.229C627.103 140.663 628.117 139.955 629.061 139.106C630.052 138.209 631.019 137.195 631.963 136.062V104.991ZM653.597 53.3968V158.19H640.219C637.483 158.19 635.785 157.484 634.935 155.03L633.237 149.439C631.774 150.996 630.24 152.411 628.636 153.685C627.032 154.959 625.286 156.068 623.399 157.012C621.559 157.908 619.553 158.593 617.383 159.064C615.259 159.583 612.947 159.843 610.447 159.843C606.625 159.843 603.086 158.994 599.83 157.295C596.575 155.596 593.767 153.143 591.408 149.934C589.049 146.726 587.185 142.809 585.817 138.185C584.495 133.514 583.835 128.206 583.835 122.261C583.835 116.787 584.59 111.715 586.1 107.044C587.61 102.326 589.733 98.2677 592.47 94.8705C595.253 91.4261 598.58 88.7366 602.449 86.802C606.318 84.8675 610.588 83.9002 615.259 83.9002C619.081 83.9002 622.29 84.4428 624.885 85.5281C627.48 86.6133 630.292 88.3457 632.416 90.1859V53.3968H653.597Z" fill="#E62B28"></path>
                                    <path d="M687.042 84.6118V158.19H664.745V84.6118H687.042Z" fill="#E62B28"></path>
                                    <path d="M732.368 84.1833C737.936 84.1833 743.008 85.0562 747.585 86.802C752.162 88.5478 756.078 91.0486 759.334 94.3043C762.637 97.56 765.185 101.523 766.978 106.195C768.818 110.819 769.738 116.033 769.738 121.836C769.738 127.687 768.818 132.972 766.978 137.69C765.185 142.361 762.637 146.348 759.334 149.651C756.078 152.907 752.162 155.431 747.585 157.224C743.008 158.97 737.936 159.843 732.368 159.843C726.754 159.843 721.634 158.97 717.01 157.224C712.433 155.431 708.47 152.907 705.12 149.651C701.817 146.348 699.245 142.361 697.405 137.69C695.612 132.972 694.716 127.687 694.716 121.836C694.716 116.033 695.612 110.819 697.405 106.195C699.245 101.523 701.817 97.56 705.12 94.3043C708.47 91.0486 712.433 88.5478 717.01 86.802C721.634 85.0562 726.754 84.1833 732.368 84.1833ZM732.368 143.564C737.417 143.564 741.121 141.795 743.48 138.256C745.887 134.67 747.09 129.244 747.09 121.978C747.09 114.711 745.887 109.309 743.48 105.77C741.121 102.231 737.417 100.462 732.368 100.462C727.178 100.462 723.38 102.231 720.973 105.77C718.567 109.309 717.364 114.711 717.364 121.978C717.364 129.244 718.567 134.67 720.973 138.256C723.38 141.795 727.178 143.564 732.368 143.564Z" fill="#E62B28"></path>
                                    <path d="M242.28 127.131C246.869 127.131 250.177 125.999 252.203 123.734C254.289 121.41 255.332 118.43 255.332 114.794C255.332 111.04 254.289 108.089 252.203 105.944C250.177 103.798 246.869 102.726 242.28 102.726C237.691 102.726 234.354 103.798 232.268 105.944C230.241 108.089 229.228 111.04 229.228 114.794C229.228 116.582 229.467 118.221 229.943 119.711C230.48 121.201 231.284 122.512 232.357 123.644C233.43 124.717 234.771 125.581 236.38 126.237C238.049 126.833 240.015 127.131 242.28 127.131ZM263.735 181.752C263.735 179.547 262.752 177.968 260.785 177.014C258.818 176.001 256.256 175.286 253.097 174.868C249.938 174.451 246.392 174.213 242.459 174.153C238.585 174.034 234.711 173.796 230.837 173.438C229.109 174.63 227.679 175.971 226.546 177.461C225.474 178.891 224.937 180.53 224.937 182.378C224.937 183.689 225.205 184.881 225.742 185.954C226.338 187.026 227.351 187.95 228.781 188.725C230.212 189.5 232.119 190.096 234.503 190.513C236.946 190.99 240.015 191.228 243.71 191.228C247.703 191.228 250.981 190.99 253.544 190.513C256.107 190.036 258.133 189.381 259.623 188.546C261.172 187.712 262.245 186.699 262.841 185.507C263.437 184.374 263.735 183.123 263.735 181.752ZM291.09 88.5117V98.6135C291.09 100.163 290.643 101.385 289.75 102.279C288.915 103.173 287.455 103.858 285.369 104.335L278.664 105.855C279.022 107.166 279.29 108.507 279.469 109.877C279.707 111.248 279.827 112.678 279.827 114.168C279.827 118.817 278.843 122.989 276.876 126.684C274.969 130.379 272.317 133.538 268.92 136.16C265.583 138.723 261.619 140.719 257.03 142.149C252.441 143.52 247.525 144.206 242.28 144.206C240.85 144.206 239.449 144.176 238.078 144.116C236.708 143.997 235.367 143.818 234.056 143.58C232.029 144.831 231.016 146.232 231.016 147.781C231.016 149.331 231.851 150.463 233.519 151.178C235.188 151.834 237.393 152.311 240.135 152.609C242.876 152.847 245.975 153.026 249.432 153.145C252.948 153.205 256.524 153.413 260.159 153.771C263.795 154.069 267.341 154.605 270.798 155.38C274.314 156.155 277.443 157.406 280.184 159.135C282.926 160.863 285.131 163.187 286.799 166.108C288.468 168.968 289.303 172.634 289.303 177.103C289.303 181.275 288.289 185.358 286.263 189.351C284.237 193.344 281.257 196.89 277.323 199.989C273.39 203.088 268.563 205.561 262.841 207.409C257.12 209.316 250.564 210.269 243.174 210.269C235.903 210.269 229.616 209.584 224.311 208.213C219.067 206.843 214.686 205.025 211.17 202.76C207.714 200.555 205.151 197.992 203.482 195.072C201.813 192.152 200.979 189.112 200.979 185.954C200.979 181.841 202.171 178.415 204.555 175.673C206.939 172.932 210.276 170.726 214.567 169.058C212.303 167.687 210.515 165.929 209.203 163.783C207.892 161.638 207.237 158.867 207.237 155.469C207.237 154.099 207.475 152.668 207.952 151.178C208.429 149.629 209.174 148.139 210.187 146.709C211.26 145.219 212.571 143.818 214.12 142.507C215.67 141.196 217.517 140.034 219.663 139.021C214.835 136.458 211.021 133.091 208.22 128.919C205.419 124.687 204.018 119.771 204.018 114.168C204.018 109.52 205.002 105.348 206.969 101.653C208.935 97.8983 211.617 94.7396 215.014 92.1769C218.471 89.5546 222.524 87.5581 227.172 86.1874C231.821 84.757 236.857 84.0419 242.28 84.0419C250.147 84.0419 257.12 85.5318 263.199 88.5117H291.09Z" fill="#E62B28"></path>
                                    <path d="M688.198 71.2462C688.914 69.688 689.271 68.0352 689.271 66.2881C689.271 64.4937 688.914 62.8173 688.198 61.259C687.483 59.7008 686.505 58.3313 685.265 57.1508C684.025 55.9703 682.571 55.0495 680.901 54.3884C679.28 53.7273 677.539 53.3968 675.679 53.3968C673.867 53.3968 672.173 53.7273 670.6 54.3884C669.026 55.0495 667.619 55.9703 666.379 57.1508C665.186 58.3313 664.233 59.7008 663.517 61.259C662.849 62.8173 662.516 64.4937 662.516 66.2881C662.516 68.0352 662.849 69.688 663.517 71.2462C664.233 72.7573 665.186 74.1031 666.379 75.2836C667.619 76.4169 669.026 77.3377 670.6 78.046C672.173 78.7071 673.867 79.0377 675.679 79.0377C677.539 79.0377 679.28 78.7071 680.901 78.046C682.571 77.3377 684.025 76.4169 685.265 75.2836C686.505 74.1031 687.483 72.7573 688.198 71.2462Z" fill="#E62B28"></path>
                                </svg>
                                <p class="text-gray-400 mt-0 mb-0 text-right flex-1">
                                    <strong>Senior Software Engineer</strong> (<em>2026 &mdash; Present</em>)
                                </p>
                            </div>
                        </div>

                        {{-- Future Publishing --}}
                        <div class="relative">
                            <div class="absolute -left-[2.05rem] print:-left-8 top-14 print:size-3 print:top-11 size-4 rounded-full bg-gray-400 border-4 border-white print:border-2 print:border-gray-400 print:bg-gray-400 outline-2 outline outline-gray-200" style="print-color-adjust: exact; -webkit-print-color-adjust: exact;"></div>
                            <div class="flex items-center gap-4 mb-2">
                                <img class="no-shadow w-32 print:w-24 shrink-0" src="{{ asset('/images/cv/future-plc.webp') }}" />
                                <p class="text-gray-400 mt-0 mb-0 text-right flex-1">
                                    <strong>Senior Tech Lead</strong> (<em>2026</em>)
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
