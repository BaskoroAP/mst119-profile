<?php

namespace BMI\Plugin\Checker;

/**
 * Class DomainParser
 *
 * Handles domain string parsing and comparison operations.
 * Extracts domain from URLs and handles protocol/www prefix removal.
 */
class DomainParser
{
    const HTTPS_PREFIX = 'https://';
    const HTTP_PREFIX = 'http://';
    const WWW_PREFIX = 'www.';

    /**
     * Parses a domain string to remove protocol and optionally 'www.'.
     *
     * @param string $domain The domain/URL to parse.
     * @param bool $removeWWW Whether to remove 'www.' prefix. Default true.
     * @return string The parsed domain.
     */
    public function parse($domain, $removeWWW = true)
    {
        // Remove https://
        if ($this->startsWith($domain, self::HTTPS_PREFIX)) {
            $domain = substr($domain, strlen(self::HTTPS_PREFIX));
        }

        // Remove http://
        if ($this->startsWith($domain, self::HTTP_PREFIX)) {
            $domain = substr($domain, strlen(self::HTTP_PREFIX));
        }

        // Remove www. if requested
        if ($removeWWW && $this->startsWith($domain, self::WWW_PREFIX)) {
            $domain = substr($domain, strlen(self::WWW_PREFIX));
        }

        // Remove trailing slash
        return rtrim($domain, '/');
    }

    /**
     * Compares two domains for equality after parsing.
     *
     * @param string $domain1 First domain to compare.
     * @param string $domain2 Second domain to compare.
     * @param bool $removeWWW Whether to remove 'www.' before comparing.
     * @return bool True if domains are equal, false otherwise.
     */
    public function areEqual($domain1, $domain2, $removeWWW = true)
    {
        $parsed1 = $this->parse($domain1, $removeWWW);
        $parsed2 = $this->parse($domain2, $removeWWW);

        return strcasecmp($parsed1, $parsed2) === 0;
    }

    /**
     * Extracts the protocol from a URL.
     *
     * @param string $url The URL to extract protocol from.
     * @return string The protocol ('https://' or 'http://'), or empty string if none found.
     */
    public function extractProtocol($url)
    {
        if ($this->startsWith($url, self::HTTPS_PREFIX)) {
            return self::HTTPS_PREFIX;
        }

        if ($this->startsWith($url, self::HTTP_PREFIX)) {
            return self::HTTP_PREFIX;
        }

        return '';
    }

    /**
     * Checks if a domain has www prefix.
     *
     * @param string $domain The domain to check.
     * @return bool True if has www prefix.
     */
    public function hasWwwPrefix($domain)
    {
        $withoutProtocol = $this->parse($domain, false);
        return $this->startsWith($withoutProtocol, self::WWW_PREFIX);
    }

    /**
     * Builds search and replace arrays for domain migration.
     *
     * @param string $sourceDomain The source domain (from backup).
     * @param string $targetDomain The target domain (current site).
     * @param string $sourceAbspath The source ABSPATH.
     * @param string $targetAbspath The target ABSPATH.
     * @param bool $useSSL Whether the target site uses SSL.
     * @return array An array with 'search' and 'replace' keys containing the arrays.
     */
    public function buildSearchReplacePairs(
        $sourceDomain,
        $targetDomain,
        $sourceAbspath,
        $targetAbspath,
        $useSSL
    ) {
        $parsedSource = $this->parse($sourceDomain);
        $parsedTarget = $this->parse($targetDomain, false);
        $ssl = $useSSL ? self::HTTPS_PREFIX : self::HTTP_PREFIX;

        $searchArray = [
            $sourceAbspath,
            self::HTTPS_PREFIX . self::WWW_PREFIX . $parsedSource,
            self::HTTP_PREFIX . self::WWW_PREFIX . $parsedSource,
            self::HTTPS_PREFIX . $parsedSource,
            self::HTTP_PREFIX . $parsedSource,
            self::WWW_PREFIX . $parsedSource,
            $parsedSource
        ];

        $replaceArray = [
            $targetAbspath,
            $ssl . self::WWW_PREFIX . $parsedTarget,
            $ssl . self::WWW_PREFIX . $parsedTarget,
            $ssl . $parsedTarget,
            $ssl . $parsedTarget,
            self::WWW_PREFIX . $parsedTarget,
            $parsedTarget
        ];

        // Remove ABSPATH pair if they are the same
        if ($sourceAbspath === $targetAbspath) {
            array_shift($searchArray);
            array_shift($replaceArray);
        }

        return [
            'search' => $searchArray,
            'replace' => $replaceArray
        ];
    }

    /**
     * Checks if string starts with a prefix.
     *
     * @param string $string The string to check.
     * @param string $prefix The prefix to look for.
     * @return bool True if string starts with prefix.
     */
    private function startsWith($string, $prefix)
    {
        return strncmp($string, $prefix, strlen($prefix)) === 0;
    }
}
