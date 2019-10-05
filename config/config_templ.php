<?php
if (!defined("FUNC_FILE")) die("Illegal file access");

$conftp = array();
$conftp['gif'] = <<<HTML
<a rel="[rel]" title="[title]" href="[src]" class="screens"><img src="[tsrc]" style="width: [twidth]px; float: [align]; margin: 0 5px 5px 0;" alt="[title]"></a>
HTML;
$conftp['jpg'] = <<<HTML
<a rel="[rel]" title="[title]" href="[src]" class="screens"><img src="[tsrc]" style="width: [twidth]px; float: [align]; margin: 0 5px 5px 0;" alt="[title]"></a>
HTML;
$conftp['jpeg'] = <<<HTML
<a rel="[rel]" title="[title]" href="[src]" class="screens"><img src="[tsrc]" style="width: [twidth]px; float: [align]; margin: 0 5px 5px 0;" alt="[title]"></a>
HTML;
$conftp['png'] = <<<HTML
<a rel="[rel]" title="[title]" href="[src]" class="screens"><img src="[tsrc]" style="width: [twidth]px; float: [align]; margin: 0 5px 5px 0;" alt="[title]"></a>
HTML;
$conftp['bmp'] = <<<HTML
<a rel="[rel]" title="[title]" href="[src]" class="screens"><img src="[tsrc]" style="width: [twidth]px; float: [align]; margin: 0 5px 5px 0;" alt="[title]"></a>
HTML;
$conftp['mp3'] = <<<HTML
<audio controls><source src="[src]" type="audio/mpeg"></audio>
HTML;
$conftp['wav'] = <<<HTML
<audio controls><source src="[src]" type="audio/wav"></audio>
HTML;
$conftp['wave'] = <<<HTML
<audio controls><source src="[src]" type="audio/wav"></audio>
HTML;
$conftp['mp4'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/mp4"></video>
HTML;
$conftp['m4a'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/mp4"></video>
HTML;
$conftp['m4p'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/mp4"></video>
HTML;
$conftp['m4b'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/mp4"></video>
HTML;
$conftp['m4r'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/mp4"></video>
HTML;
$conftp['m4v'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/mp4"></video>
HTML;
$conftp['ogg'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/ogg"></video>
HTML;
$conftp['oga'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/ogg"></video>
HTML;
$conftp['ogv'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/ogg"></video>
HTML;
$conftp['ogx'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/ogg"></video>
HTML;
$conftp['spx'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/ogg"></video>
HTML;
$conftp['opus'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/ogg"></video>
HTML;
$conftp['webm'] = <<<HTML
<video width="[width]" height="[height]" controls><source src="[src]" type="video/webm"></video>
HTML;
$conftp['zip'] = <<<HTML
<a href="[src]" target="_blank" title="[title]">[title]</a>
HTML;
$conftp['rar'] = <<<HTML
<a href="[src]" target="_blank" title="[title]">[title]</a>
HTML;
$conftp['gzip'] = <<<HTML
<a href="[src]" target="_blank" title="[title]">[title]</a>
HTML;
$conftp['7zip'] = <<<HTML
<a href="[src]" target="_blank" title="[title]">[title]</a>
HTML;
$conftp['swf'] = <<<HTML
 <embed src="[src]" height="[height]" width="[width]" type="application/x-shockwave-flash">
HTML;

?>