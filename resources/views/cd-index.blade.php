<x-cd.layout.app title=":project_name" subtitle="Custom Development">
    <h1>Starter Template</h1>
    <p class="intro">A basic starting point for your HTML markup or theme templates</p>
    <p>This template has only baseline CSS and JavaScript loaded. A default right sidebar layout is used,
        but can be customized using the body classes below. </p>
    <hr class="invisible">
    <h2>Browser Support</h2>
    <p>For the 2017 CSS Framework, it can be assumed that all reasonably-modern browsers are supported. This
        would primarily include recent versions of Safari (desktop and iOS), Chrome (desktop and Android),
        Firefox, and Edge. More esoteric browsers like Opera and IE Mobile are untested, but will typically
        work well if up-to-date. Internet Explorer 11 is supported, but all older versions are not. It's
        possible that designs will work acceptably well on older versions of these browsers. However, web
        hosting companies (like Pantheon, which hosts many of our Drupal sites) are moving aggressively
        toward secure hosting with newer standards (TLS 1.1+), which make secure-hosted websites 100%
        incompatible with older browsers that do not support these standards.</p>
    <p>As of September 2017, this means the following browsers <strong>cannot</strong> connect to our
        secure-hosted sites:</p>
    <ul>
        <li>Any version of Internet Explorer before IE11 (about 4 years ago)</li>
        <li>Safari on any MacOS prior to 10.9 (about 4 years ago)</li>
        <li>iOS older than 5 (~6 years ago - a security pioneer!)</li>
        <li>Android's default browser prior to v5 (only about 3 years ago - yikes!)</li>
        <li>Chrome about 5 years ago</li>
        <li>Firefox a little under 4 years ago</li>
    </ul>
    <p>Given this, and the overall passage of time, we've decided to drop support for older browsers and
        embrace modern CSS options like <a href="http://caniuse.com/#feat=flexbox">Flexbox layout</a>.</p>
    <hr class="section-break">
    <h2>CSS Class Options</h2>
    <h3>Body Classes <small><code>BODY<strong>.class</strong></code></small></h3>
    <h4 class="fade-bw low-margin">Cornell Branding Banner</h4>
    <ul class="list-menu vertical">
        <li><code>.cu-red</code> - <span class="smallprint">Overrides the default light gray banner to be red with white logo. Affects both 45px and Large Seal options.</span>
        </li>
        <li><code>.cu-black</code> - <span class="smallprint">Overrides the default light gray banner to be black with white logo. Affects both 45px and Large Seal options.</span>
        </li>
        <li><code>.cu-gray</code> - <span class="smallprint">Overrides the default light gray banner to be dark gray with white logo. Affects both 45px and Large Seal options.</span>
        </li>
        <li><code>.cu-45</code> - <span class="smallprint">Renders as a ~45px banner with small Cornell logo (this is also the default if no body class is defined). Headings appear below the banner. <strong>Note:</strong> This class is mutually exclusive with .cu-seal.</span>
        </li>
        <li><code>.cu-seal</code> - <span class="smallprint">Renders as a taller banner with a large standalone Cornell seal. Headings appear to the right of the seal. <strong>Note:</strong> This class is mutually exclusive with .cu-45.</span>
        </li>
        <li><code>.cu-seal-right</code> - <span class="smallprint">Added to .cu-seal to swap the left and right positions of the seal and headings.</span>
        </li>
        <li><code>.cu-45-mobile</code> - <span class="smallprint">Added to .cu-seal to switch to 45px style at mobile sizes.</span>
        </li>
        <li><code>.cu-45-mobile-red</code> - <span class="smallprint">Added to .cu-seal-mobile also activate the red color option on the 45px banner at mobile sizes.</span>
        </li>
        <li><code>.no-main-nav</code> - <span class="smallprint">When .cu-seal is in use and no main navigation bar is planned, this class can be included to apply some subtle adjustments to the Cornell header.</span>
        </li>
    </ul>
    <h4 id="classes-body-layout" class="fade-bw low-margin">Layout</h4>
    <ul class="list-menu vertical half-margin">
        <li><code>.sidebar</code> - <span class="smallprint">Activates a 65/35 two-column layout, applied to any container pairs with either a <code
                    class="text-black">.primary</code> (~65% width) or <code
                    class="text-black">.secondary</code> (35% width) class. This is primarily used by the main article, where the sidebar (aligned left by default) is comprised of <code
                    class="text-black">#sidebar-top</code> and <code
                    class="text-black">#sidebar-bottom</code>. They appear as a single column, but at mobile size, <code
                    class="text-black">#sidebar-top</code> appears above the main article while <code
                    class="text-black">#sidebar-bottom</code> appears below. The same proportion can be used elsewhere in the design, wherever <code
                    class="text-black">.primary</code> and <code class="text-black">.secondary</code> are paired. Examples of this can be seen in the supplementary and footer regions below.</span>
        </li>
        <li><code>.sidebar-right</code> - <span class="smallprint">Displays the sidebar on the right. (requires <code
                    class="text-black">.sidebar</code>)</span></li>
        <li><code>.sidebar-tint</code> - <span class="smallprint">Sidebar is tinted a light gray, and padding adjusted. (requires <code
                    class="text-black">.sidebar</code>)</span></li>
        <li><code>.sidebar-tint-edge</code> - <span class="smallprint">Sidebar tinting is continued horizontally to the edge of the screen. (requires <code
                    class="text-black">.sidebar</code> and <code
                    class="text-black">.sidebar-tint</code>)</span></li>
        <li><code>.sidebar-tint-fade</code> - <span class="smallprint">Sidebar tinting fades to white as it approaches the top left corner. This can be useful for designs without a solid, dark nav bar. (requires <code
                    class="text-black">.sidebar</code> and <code class="text-black">.sidebar-tint</code>, and works best with <code
                    class="text-black">.sidebar-right</code> and <code
                    class="text-black">.sidebar-tint-edge</code>)</span></li>
        <li><code>.one-column-article</code> - <span class="smallprint">Creates a single column design with a modern news article feel (limited max content width, figures and blockquotes offset into the gutters). As a compliment to this, a <code
                    class="text-black">.full-window</code> image class was added to make images within the main article (typically a WYSIWYG region) break out of the content container to the full width of the window. Both the <code
                    class="text-black">.one-column-article</code> and the <code class="text-black">.full-window</code> image should not be paired with any of the sidebar layouts and options above.</span>
        </li>
    </ul>
    <aside class="smallprint"><strong>Note:</strong> Body layout options affect tablet and desktop only.
        They have no effect on the single-column mobile layout.
    </aside>
    <hr class="invisible">
    <h3>Cornell Seal Color <small><code>H1.cu-logo<strong>.class</strong></code></small></h3>
    <ul class="list-menu vertical">
        <li><code>.black</code> - <span class="smallprint">Overrides the default red seal with a black version. This works on both 45px (<code>body.cu-45</code>) and Large Seal (<code>body.cu-seal</code>) banners. Be careful, as this also overrides the white version used on red, black, and gray Cornell themes.</span>
        </li>
    </ul>
    <hr class="invisible">
    <h3>College/School Unit Signature <small class="fade-bw"><em>(Advanced Option)</em></small></h3>
    <p class="smallprint">Renders a responsive, modernized version of the classic Cornell unit signature.
        This is a rarely-needed option, since only college-level units at Cornell are allowed to utilize a
        branding lockup with the University seal. And of those units, only a subset will want this
        traditional layout. An SVG graphic of the unit name must be created, and two markup changes must be
        made:</p>
    <ul class="list-menu vertical">
        <li><code>.cu-unit-signature</code> - <span
                class="smallprint">Add this class to <code>DIV.cu-brand</code>.</span></li>
        <li><code>.cu-unit-lockup</code> - <span class="smallprint">A DIV with this class replaces <code>DIV.cu-unit</code> or is added as a previous sibling if .cu-unit will still be utilized.</span>
        </li>
    </ul>
    <h4 class="fade-bw low-margin">Unit Image</h4>
    <p class="smallprint">To match the expected style, unit logotype images must be made with Iowan Old
        Style 35pt, and the SVG canvas should match the size of the Cornell University logotype (480x36) so
        that character sizes scale uniformly. When done correctly, the final rendering will look like the
        image below:</p>
    <p><img src="{{ asset('cwd-framework/images/cornell/unit_signature_sample.png') }}" width="571"
            height="120" alt="The final rendering of the Cornell seal and unit logotype"></p>
    <hr class="section-break">
    <h2>Curios</h2>
    <h3>What is .cu45-helper?</h3>
    <p>An extra div (<code>&lt;div class="cu45-helper"&gt;&lt;/div&gt;</code>) appears in the Cornell
        Branding header, to facilitate the use of the 45px banner while still allowing unit heading text to
        be displayed. A brief technical explanation is that .cu45-helper is a full-viewport-width, relative
        target for an absolutely-positioned ~45px :before pseudo class, allowing unit headings to drop below
        the Cornell logo without requiring separate HTML markup or hardcoded heights.</p>
</x-cd.layout.app>
