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
                list($article_id, $clang) = $item;

                // generate content
                // temporarily disabled, see https://github.com/FriendsOfREDAXO/cache_warmup/issues/65
                // $article = new rex_article_content($article_id, $clang);
                // $article->getArticle();

                // generate meta
                rex_article_cache::generateMeta($article_id, $clang);

                // generate lists
                rex_article_cache::generateLists($article_id);
            }
        }
        return $items;
    }
}
