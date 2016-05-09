<?php

/**
 * Class cache_warmup_generator_pages
 */
class cache_warmup_generator_pages extends cache_warmup_generator
{

    /**
     * Generate cache for given items
     *
     * @param array $items
     * @return array
     */
    public function generateCache(array $items)
    {
        if (rex_addon::get('structure')->isAvailable()) {
            foreach ($items as $item) {

                // generate content
                $article = new rex_article_content($item[0], $item[1]);
                $content = $article->getArticle();

                // generate meta
                rex_article_cache::generateMeta($item[0], $item[1]);

                // generate lists
                rex_article_cache::generateLists($item[0]);
            }
        }
        return $items;
    }
}