<?php //search every thread in the forum
/* ====================================================================================================================== */
/* NoNonsense Forum v26 © Copyright (CC-BY) Kroc Camen 2010-2015
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//bootstrap the forum; you should read that file first
require_once './start.php';

/* ======================================================================================================================
   gather the matches
   ====================================================================================================================== */
//what was searched for (the search box submits `?q=`)
//(`(string)` cast: PHP8 deprecates passing `null` to `mb_substr`)
$Q = mb_substr ((string) @$_GET['q'], 0, 100); safeTrim ($Q);

//collect every thread file below the forum root, skipping the private / reserved folders and anything hidden.
//(`.rss` files are NNF's storage format: one per thread, one `<item>` per post)
function searchFiles ($dir, array &$files) {
        if (!is_dir ($dir) || !($entries = @scandir ($dir))) return;
        foreach ($entries as $entry) if ($entry[0] !== '.') {
                $path = $dir.DIRECTORY_SEPARATOR.$entry;
                if (is_dir ($path)) {
                        if (in_array (strtolower ($entry), array ('users', 'themes', 'lib', 'cgi-bin'), true)) continue;
                        searchFiles ($path, $files);
                } else if (strtolower (substr ($entry, -4)) === '.rss') {
                        $files[] = $path;
                }
        }
}

$results = array (); $truncated = false;
if ($Q !== '') {
        //a broad query (say, a single common letter) would otherwise collect a match for every post in the forum;
        //stop after a reasonable number so that memory use stays bounded
        $max = 500;
        
        $files = array (); searchFiles (FORUM_ROOT, $files);
        foreach ($files as $path) if ($xml = @simplexml_load_file ($path)) {
                //the sub-forum the thread is in, relative to the forum root ('' for the root forum)
                $dir   = dirname ($path);
                $forum = $dir === FORUM_ROOT ? '' : substr ($dir, strlen (FORUM_ROOT) + 1).'/';
                
                $title = (string) $xml->channel->title;
                $items = $xml->channel->item;
                $count = count ($items);
                //does the thread title itself match? (only reported once, against the first post)
                $on_title = mb_stripos ($title, $Q) !== false;
                
                for ($i = 0; $i < $count; $i++) {
                        $item   = $items[$i];
                        $author = (string) $item->author;
                        $text   = (string) $item->description;
                        //the *last* item is the first post, the one that carries the thread title
                        if (!(
                                ($on_title && $i === $count-1)
                                || mb_stripos ($author, $Q) !== false
                                || mb_stripos (unformatText ($text), $Q) !== false
                        )) continue;
                        
                        $link = (string) $item->link;
                        $results[] = array (
                                //(`url`s are stored absolute in the RSS; keep just the path + fragment)
                                'link'          => substr ($link, (int) strpos ($link, '/', 9)),
                                'title'         => $title,
                                'author'        => $author,
                                'date'          => (int) strtotime ((string) $item->pubDate),
                                'snippet'       => searchSnippet ($text, $Q),
                                'forum'         => $forum
                        );
                        
                        if (count ($results) >= $max) { $truncated = true; break 2; }
                }
        }
        
        //newest first
        usort ($results, function ($a, $b) { return $b['date'] - $a['date']; });
}

//work out the pages and take the slice for this one
$FOUND = count ($results);
$PAGES = max (1, (int) ceil ($FOUND / FORUM_SEARCH));
$PAGE  = !PAGE || PAGE > $PAGES ? 1 : PAGE;
$results = array_slice ($results, ($PAGE-1) * FORUM_SEARCH, FORUM_SEARCH);

/* ======================================================================================================================
   template the page
   ====================================================================================================================== */
$template = prepareTemplate (
        //template, no canonical URL      //HTML title
        THEME_ROOT.'search.html', '', sprintf (THEME_TITLE, @$LANG[LANG]['title_search'] ?: 'Search', '')
);

//put the query back into the search box
$template->setValue ('input#query@value', $Q);

//summarise what was found
if ($Q === '') {
        $summary = @$LANG[LANG]['search_prompt'] ?: 'Search every discussion in this forum.';
} else if (!$FOUND) {
        $summary = sprintf (@$LANG[LANG]['search_none'] ?: 'No results for “%1$s”.', $Q);
} else {
        $summary = sprintf (
                $FOUND === 1
                        //the singular / plural formats differ in most languages
                        ? (@$LANG[LANG]['search_result']  ?: '%1$u result for “%2$s”.')
                        : (@$LANG[LANG]['search_results'] ?: '%1$u results for “%2$s”.'),
                $FOUND, $Q
        );
        if ($truncated) $summary .= ' '.(@$LANG[LANG]['search_limited']
                ?: 'Too many matches; only the first results are shown. Try a more specific query.');
}
$template->setValue ('#nnf_search-summary', $summary);

/* results
   ---------------------------------------------------------------------------------------------------------------------- */
if ($results) {
        //page links; `theme_pageList` normally links to a thread, so give it a callback that builds search URLs
        theme_pageList ($template, '', $PAGE, $PAGES, function ($page) use ($Q) {
                return FORUM_PATH.'search.php?q='.urlencode ($Q).($page > 1 ? '&page='.$page : '');
        });
        
        $item = $template->repeat ('.nnf_result');
        foreach ($results as $result) {
                $item->set (array (
                        'a.nnf_result-title'            => $result['title'],
                        'a.nnf_result-title@href'       => $result['link'],
                        '.nnf_result-author'            => $result['author'],
                        'time.nnf_result-time'          => date (DATE_FORMAT, $result['date']),
                        'time.nnf_result-time@datetime' => date ('c', $result['date']),
                        'a.nnf_result-forum-name'       => rtrim ($result['forum'], '/'),
                        'a.nnf_result-forum-name@href'  => url ($result['forum'])
                ))->remove (array (
                        //a thread in the root forum has no sub-forum to name
                        '.nnf_result-forum'             => $result['forum'] === '',
                        //mark the author as a moderator?
                        '.nnf_result-author@class'      => isMod ($result['author']) ? false : 'nnf_mod'
                ));
                
                try {   //insert the excerpt, dealing with an invalid HTML error
                        $item->setValue ('.nnf_result-text', $result['snippet'], true);
                } catch (Exception $e) {
                        $item->setValue ('.nnf_result-text', THEME_HTML_ERROR, true);
                }
                
                $item->next ();
        }
} else {
        //nothing to list (or nothing searched for yet): remove the list and its page links
        $template->remove (array ('#nnf_search/ol' => true, '#nnf_search/nav' => true));
}

//call the theme-specific templating function, in 'theme.php', before outputting
theme_custom ($template);
output ($template);

?>