<?php

/**
 * Class cache_warmup_generator_pages.
 */
class cache_warmup_generator_pages extends cache_warmup_generator
{
    /**
     * Generate cache for given items.
     *
     * @param array $items
     * @return array
     */
    public function generateCache(array $items): array
    {
        if ($items && !rex_addon::get('structure')->isAvailable()) {
            throw new RuntimeException('The structure AddOn is not available');
        }
        if (rex_addon::get('structure')->isAvailable()) {
            $structure = rex_addon::get('structure');
            $previousArticleId = rex_article::getCurrentId();
            $previousClangId = rex_clang::getCurrentId();

            try {
                foreach ($items as $item) {
                    [$article_id, $clang] = $item;
                    if (!ctype_digit((string) $clang)) {
                        throw new InvalidArgumentException('Invalid article language: ' . $clang);
                    }

                    // Templates can read the global context instead of this content object.
                    rex_clang::setCurrentId((int) $clang);
                    $structure->setProperty('article_id', (int) $article_id);

                    // generate content
                    cache_warmup_profiles::run($item[2] ?? 'original', static function () use ($article_id, $clang) {
                        $article = new rex_article_content($article_id, $clang);
                        $article->getArticleTemplate();
                    });

                    // Core methods also return false/error strings without throwing.
                    if (true !== rex_article_cache::generateMeta($article_id, $clang)) {
                        throw new RuntimeException('Could not generate article metadata: ' . $article_id . '.' . $clang);
                    }
                    if (true !== rex_article_cache::generateLists($article_id)) {
                        throw new RuntimeException('Could not generate article lists: ' . $article_id);
                    }
                }
            } finally {
                $structure->setProperty('article_id', $previousArticleId);
                rex_clang::setCurrentId($previousClangId);
            }
        }
        return $items;
    }
}
