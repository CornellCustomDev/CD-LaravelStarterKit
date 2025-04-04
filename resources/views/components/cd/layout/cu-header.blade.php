<header id="cu-header" aria-label="Site banner">
    <div id="cu-search" class="cu-search">
        <div class="container-fluid">
            <form id="cu-search-form" tabindex="-1" role="search" action="https://www.cornell.edu/search/">
                <label for="cu-search-query" class="sr-only">Search:</label>
                <input type="text" id="cu-search-query" name="q" value="" size="30">
                <button name="btnG" id="cu-search-submit" type="submit" value="go"><span class="sr-only">Submit Search</span></button>

                <fieldset class="search-filters" role="radiogroup">
                    <legend class="sr-only">Search Filters</legend>
                    <input type="radio" id="cu-search-filter1" name="sitesearch" value="thissite" checked="checked">
                    <label for="cu-search-filter1"><span class="sr-only">Search </span>This Site</label>
                    <input type="radio" id="cu-search-filter2" name="sitesearch" value="cornell">
                    <label for="cu-search-filter2"><span class="sr-only">Search </span>Cornell</label>
                </fieldset>
            </form>
        </div>
    </div>
    <div class="cu45-helper"></div>
    <div class="container-fluid cu-brand">
        <h1 class="cu-logo"><a href="https://www.cornell.edu"><img class="sr-only" src="{{ asset('cwd-framework/images/cornell/bold_cornell_logo_simple_b31b1b.svg') }}" alt="Cornell University" width="245" height="62"></a></h1>
        <div class="cu-unit">
            <h2>{{ $title }}</h2>
            <h3 class="sans">{{ $subtitle }}</h3>
        </div>
        <div class="buttons">
            <button class="mobile-button" id="mobile-nav">Main Menu</button>
            <button class="mobile-button" id="cu-search-button">Toggle Search Form</button>
        </div>
    </div>
</header>
