<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMXPath;

class SeoAuditService
{
    protected string $baseUrl;
    protected array $auditResults = [];
    protected array $errors = [];

    /**
     * Run a comprehensive SEO audit
     */
    public function runAudit(string $url, string $type = 'full'): array
    {
        $this->baseUrl = $url;
        $this->auditResults = [];
        $this->errors = [];

        try {
            switch ($type) {
                case 'basic':
                    $this->runBasicAudit();
                    break;
                case 'technical':
                    $this->runTechnicalAudit();
                    break;
                case 'content':
                    $this->runContentAudit();
                    break;
                case 'full':
                default:
                    $this->runFullAudit();
                    break;
            }

            $summary = $this->generateSummary();
            $aiSummary = $this->generateAISummary([
                'url' => $url,
                'audit_type' => $type,
                'results' => $this->auditResults,
                'summary' => $summary
            ]);

            return [
                'success' => true,
                'url' => $url,
                'audit_type' => $type,
                'timestamp' => now()->toISOString(),
                'results' => $this->auditResults,
                'errors' => $this->errors,
                'summary' => $summary,
                'ai_summary' => $aiSummary
            ];

        } catch (\Exception $e) {
            Log::error('SEO audit failed', [
                'url' => $url,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'url' => $url,
                'audit_type' => $type,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Run basic SEO audit
     */
    private function runBasicAudit(): void
    {
        $this->checkPageSpeed();
        $this->checkMetaTags();
        $this->checkHeadings();
        $this->checkImages();
        $this->checkLinks();
    }

    /**
     * Run technical SEO audit
     */
    private function runTechnicalAudit(): void
    {
        $this->runBasicAudit();
        $this->checkRobotsTxt();
        $this->checkSitemap();
        $this->checkHttps();
        $this->checkMobileResponsiveness();
        $this->checkStructuredData();
    }

    /**
     * Run content SEO audit
     */
    private function runContentAudit(): void
    {
        $this->runBasicAudit();
        $this->checkContentQuality();
        $this->checkKeywordDensity();
        $this->checkReadability();
        $this->checkDuplicateContent();
    }

    /**
     * Run full comprehensive audit
     */
    private function runFullAudit(): void
    {
        $this->runTechnicalAudit();
        $this->runContentAudit();
        $this->checkSocialMedia();
        $this->checkAnalytics();
        $this->checkSecurity();
        $this->checkAccessibility();
    }

    /**
     * Check page speed using Google PageSpeed Insights API
     */
    private function checkPageSpeed(): void
    {
        try {
            $apiKey = config('services.google.pagespeed_key');
            if (!$apiKey) {
                $this->addResult('page_speed', 'warning', ['message' => 'PageSpeed API key not configured']);
                return;
            }

            $response = Http::get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', [
                'url' => $this->baseUrl,
                'key' => $apiKey,
                'strategy' => 'mobile'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $score = $data['lighthouseResult']['categories']['performance']['score'] * 100;
                
                $this->addResult('page_speed', $score >= 90 ? 'good' : ($score >= 50 ? 'warning' : 'critical'), [
                    'score' => $score,
                    'first_contentful_paint' => $data['lighthouseResult']['audits']['first-contentful-paint']['displayValue'] ?? 'N/A',
                    'largest_contentful_paint' => $data['lighthouseResult']['audits']['largest-contentful-paint']['displayValue'] ?? 'N/A',
                    'cumulative_layout_shift' => $data['lighthouseResult']['audits']['cumulative-layout-shift']['displayValue'] ?? 'N/A'
                ]);
            } else {
                $this->addResult('page_speed', 'error', ['message' => 'Failed to fetch PageSpeed data']);
            }

        } catch (\Exception $e) {
            $this->addError('page_speed', $e->getMessage());
        }
    }

    /**
     * Check meta tags
     */
    private function checkMetaTags(): void
    {
        try {
            $html = $this->fetchPageContent();
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $metaTags = [];
            $metaNodes = $xpath->query('//meta');

            foreach ($metaNodes as $meta) {
                $name = $meta->getAttribute('name') ?: $meta->getAttribute('property');
                $content = $meta->getAttribute('content');
                if ($name && $content) {
                    $metaTags[$name] = $content;
                }
            }

            $issues = [];
            $good = [];

            // Check for essential meta tags
            if (!isset($metaTags['description'])) {
                $issues[] = 'Missing meta description';
            } else {
                $descLength = strlen($metaTags['description']);
                if ($descLength < 120 || $descLength > 160) {
                    $issues[] = "Meta description length ({$descLength} chars) should be between 120-160 characters";
                } else {
                    $good[] = 'Meta description length is optimal';
                }
            }

            if (!isset($metaTags['viewport'])) {
                $issues[] = 'Missing viewport meta tag';
            } else {
                $good[] = 'Viewport meta tag present';
            }

            if (isset($metaTags['robots'])) {
                $good[] = 'Robots meta tag present';
            }

            $this->addResult('meta_tags', empty($issues) ? 'good' : 'warning', [
                'issues' => $issues,
                'good' => $good,
                'meta_tags_found' => count($metaTags)
            ]);

        } catch (\Exception $e) {
            $this->addError('meta_tags', $e->getMessage());
        }
    }

    /**
     * Check heading structure
     */
    private function checkHeadings(): void
    {
        try {
            $html = $this->fetchPageContent();
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $headings = [];
            for ($i = 1; $i <= 6; $i++) {
                $nodes = $xpath->query("//h{$i}");
                $headings["h{$i}"] = $nodes->length;
            }

            $issues = [];
            $good = [];

            if ($headings['h1'] === 0) {
                $issues[] = 'No H1 heading found';
            } elseif ($headings['h1'] > 1) {
                $issues[] = 'Multiple H1 headings found (should be only one)';
            } else {
                $good[] = 'H1 heading structure is correct';
            }

            if ($headings['h2'] === 0) {
                $issues[] = 'No H2 headings found';
            } else {
                $good[] = 'H2 headings present';
            }

            $this->addResult('headings', empty($issues) ? 'good' : 'warning', [
                'issues' => $issues,
                'good' => $good,
                'heading_count' => $headings
            ]);

        } catch (\Exception $e) {
            $this->addError('headings', $e->getMessage());
        }
    }

    /**
     * Check images for alt text
     */
    private function checkImages(): void
    {
        try {
            $html = $this->fetchPageContent();
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $images = $xpath->query('//img');
            $totalImages = $images->length;
            $imagesWithAlt = 0;
            $imagesWithoutAlt = [];

            foreach ($images as $img) {
                $alt = $img->getAttribute('alt');
                if ($alt && trim($alt) !== '') {
                    $imagesWithAlt++;
                } else {
                    $src = $img->getAttribute('src');
                    $imagesWithoutAlt[] = $src;
                }
            }

            $altTextPercentage = $totalImages > 0 ? ($imagesWithAlt / $totalImages) * 100 : 100;
            $status = $altTextPercentage >= 90 ? 'good' : ($altTextPercentage >= 70 ? 'warning' : 'critical');

            $this->addResult('images', $status, [
                'total_images' => $totalImages,
                'images_with_alt' => $imagesWithAlt,
                'images_without_alt' => count($imagesWithoutAlt),
                'alt_text_percentage' => round($altTextPercentage, 2),
                'missing_alt_images' => array_slice($imagesWithoutAlt, 0, 5) // Show first 5
            ]);

        } catch (\Exception $e) {
            $this->addError('images', $e->getMessage());
        }
    }

    /**
     * Check internal and external links
     */
    private function checkLinks(): void
    {
        try {
            $html = $this->fetchPageContent();
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $links = $xpath->query('//a[@href]');
            $totalLinks = $links->length;
            $internalLinks = 0;
            $externalLinks = 0;
            $brokenLinks = [];

            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                if (str_starts_with($href, '/') || str_starts_with($href, $this->baseUrl)) {
                    $internalLinks++;
                } else {
                    $externalLinks++;
                }
            }

            $this->addResult('links', 'good', [
                'total_links' => $totalLinks,
                'internal_links' => $internalLinks,
                'external_links' => $externalLinks,
                'broken_links' => $brokenLinks
            ]);

        } catch (\Exception $e) {
            $this->addError('links', $e->getMessage());
        }
    }

    /**
     * Check robots.txt
     */
    private function checkRobotsTxt(): void
    {
        try {
            $robotsUrl = rtrim($this->baseUrl, '/') . '/robots.txt';
            $response = Http::timeout(10)->get($robotsUrl);

            if ($response->successful()) {
                $content = $response->body();
                $hasUserAgent = str_contains($content, 'User-agent:');
                $hasDisallow = str_contains($content, 'Disallow:');
                $hasSitemap = str_contains($content, 'Sitemap:');

                $issues = [];
                $good = [];

                if (!$hasUserAgent) {
                    $issues[] = 'No User-agent directive found';
                } else {
                    $good[] = 'User-agent directive present';
                }

                if (!$hasSitemap) {
                    $issues[] = 'No Sitemap directive found';
                } else {
                    $good[] = 'Sitemap directive present';
                }

                $this->addResult('robots_txt', empty($issues) ? 'good' : 'warning', [
                    'issues' => $issues,
                    'good' => $good,
                    'content_length' => strlen($content)
                ]);
            } else {
                $this->addResult('robots_txt', 'critical', ['message' => 'Robots.txt not found or not accessible']);
            }

        } catch (\Exception $e) {
            $this->addError('robots_txt', $e->getMessage());
        }
    }

    /**
     * Check sitemap
     */
    private function checkSitemap(): void
    {
        try {
            $sitemapUrl = rtrim($this->baseUrl, '/') . '/sitemap.xml';
            $response = Http::timeout(10)->get($sitemapUrl);

            if ($response->successful()) {
                $content = $response->body();
                $hasUrls = str_contains($content, '<url>');
                $hasLoc = str_contains($content, '<loc>');

                if ($hasUrls && $hasLoc) {
                    $this->addResult('sitemap', 'good', [
                        'status' => 'Sitemap found and properly formatted',
                        'content_length' => strlen($content)
                    ]);
                } else {
                    $this->addResult('sitemap', 'warning', ['message' => 'Sitemap found but may not be properly formatted']);
                }
            } else {
                $this->addResult('sitemap', 'critical', ['message' => 'Sitemap not found']);
            }

        } catch (\Exception $e) {
            $this->addError('sitemap', $e->getMessage());
        }
    }

    /**
     * Check HTTPS
     */
    private function checkHttps(): void
    {
        $isHttps = str_starts_with($this->baseUrl, 'https://');
        
        $this->addResult('https', $isHttps ? 'good' : 'critical', [
            'status' => $isHttps ? 'HTTPS enabled' : 'HTTPS not enabled',
            'url' => $this->baseUrl
        ]);
    }

    /**
     * Check mobile responsiveness
     */
    private function checkMobileResponsiveness(): void
    {
        try {
            $html = $this->fetchPageContent();
            $hasViewport = str_contains($html, 'viewport');
            $hasMediaQueries = str_contains($html, '@media');
            $hasResponsiveImages = str_contains($html, 'max-width') || str_contains($html, 'width: 100%');

            $score = 0;
            $issues = [];
            $good = [];

            if ($hasViewport) {
                $score += 33;
                $good[] = 'Viewport meta tag present';
            } else {
                $issues[] = 'No viewport meta tag';
            }

            if ($hasMediaQueries) {
                $score += 33;
                $good[] = 'CSS media queries found';
            } else {
                $issues[] = 'No CSS media queries found';
            }

            if ($hasResponsiveImages) {
                $score += 34;
                $good[] = 'Responsive image styles found';
            } else {
                $issues[] = 'No responsive image styles found';
            }

            $status = $score >= 90 ? 'good' : ($score >= 60 ? 'warning' : 'critical');

            $this->addResult('mobile_responsiveness', $status, [
                'score' => $score,
                'issues' => $issues,
                'good' => $good
            ]);

        } catch (\Exception $e) {
            $this->addError('mobile_responsiveness', $e->getMessage());
        }
    }

    /**
     * Check structured data
     */
    private function checkStructuredData(): void
    {
        try {
            $html = $this->fetchPageContent();
            $hasJsonLd = str_contains($html, 'application/ld+json');
            $hasMicrodata = str_contains($html, 'itemtype');
            $hasRdfa = str_contains($html, 'vocab=');

            $structuredDataFound = $hasJsonLd || $hasMicrodata || $hasRdfa;

            $this->addResult('structured_data', $structuredDataFound ? 'good' : 'warning', [
                'status' => $structuredDataFound ? 'Structured data found' : 'No structured data found',
                'json_ld' => $hasJsonLd,
                'microdata' => $hasMicrodata,
                'rdfa' => $hasRdfa
            ]);

        } catch (\Exception $e) {
            $this->addError('structured_data', $e->getMessage());
        }
    }

    /**
     * Check content quality
     */
    private function checkContentQuality(): void
    {
        try {
            $html = $this->fetchPageContent();
            $text = strip_tags($html);
            $wordCount = str_word_count($text);
            $charCount = strlen($text);

            $issues = [];
            $good = [];

            if ($wordCount < 300) {
                $issues[] = "Content is too short ({$wordCount} words). Aim for at least 300 words.";
            } else {
                $good[] = "Content length is good ({$wordCount} words)";
            }

            $this->addResult('content_quality', empty($issues) ? 'good' : 'warning', [
                'word_count' => $wordCount,
                'character_count' => $charCount,
                'issues' => $issues,
                'good' => $good
            ]);

        } catch (\Exception $e) {
            $this->addError('content_quality', $e->getMessage());
        }
    }

    /**
     * Check keyword density
     */
    private function checkKeywordDensity(): void
    {
        // This is a simplified version. In a real implementation, you'd want to analyze specific keywords
        $this->addResult('keyword_density', 'info', [
            'status' => 'Keyword density analysis requires specific keywords to analyze',
            'note' => 'Consider implementing keyword analysis based on target keywords'
        ]);
    }

    /**
     * Check readability
     */
    private function checkReadability(): void
    {
        try {
            $html = $this->fetchPageContent();
            $text = strip_tags($html);
            
            // Simple readability check (Flesch Reading Ease approximation)
            $sentences = preg_split('/[.!?]+/', $text);
            $words = str_word_count($text);
            $syllables = $this->countSyllables($text);
            
            $avgWordsPerSentence = $words / max(count($sentences), 1);
            $avgSyllablesPerWord = $syllables / max($words, 1);
            
            $readabilityScore = 206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord);
            $readabilityScore = max(0, min(100, $readabilityScore));

            $level = $readabilityScore >= 80 ? 'very_easy' : 
                    ($readabilityScore >= 60 ? 'easy' : 
                    ($readabilityScore >= 40 ? 'moderate' : 
                    ($readabilityScore >= 20 ? 'difficult' : 'very_difficult')));

            $this->addResult('readability', 'good', [
                'score' => round($readabilityScore, 2),
                'level' => $level,
                'avg_words_per_sentence' => round($avgWordsPerSentence, 2),
                'avg_syllables_per_word' => round($avgSyllablesPerWord, 2)
            ]);

        } catch (\Exception $e) {
            $this->addError('readability', $e->getMessage());
        }
    }

    /**
     * Check for duplicate content
     */
    private function checkDuplicateContent(): void
    {
        // This would typically involve comparing content with other pages
        // For now, we'll provide a placeholder
        $this->addResult('duplicate_content', 'info', [
            'status' => 'Duplicate content analysis requires comparison with other pages',
            'note' => 'Consider implementing content fingerprinting for duplicate detection'
        ]);
    }

    /**
     * Check social media tags
     */
    private function checkSocialMedia(): void
    {
        try {
            $html = $this->fetchPageContent();
            $hasOpenGraph = str_contains($html, 'og:');
            $hasTwitterCard = str_contains($html, 'twitter:');

            $socialTags = [];
            if ($hasOpenGraph) $socialTags[] = 'Open Graph';
            if ($hasTwitterCard) $socialTags[] = 'Twitter Cards';

            $this->addResult('social_media', !empty($socialTags) ? 'good' : 'warning', [
                'status' => !empty($socialTags) ? 'Social media tags found' : 'No social media tags found',
                'tags_found' => $socialTags
            ]);

        } catch (\Exception $e) {
            $this->addError('social_media', $e->getMessage());
        }
    }

    /**
     * Check analytics
     */
    private function checkAnalytics(): void
    {
        try {
            $html = $this->fetchPageContent();
            $hasGoogleAnalytics = str_contains($html, 'gtag') || str_contains($html, 'ga(');
            $hasGoogleTagManager = str_contains($html, 'googletagmanager.com');

            $analytics = [];
            if ($hasGoogleAnalytics) $analytics[] = 'Google Analytics';
            if ($hasGoogleTagManager) $analytics[] = 'Google Tag Manager';

            $this->addResult('analytics', !empty($analytics) ? 'good' : 'warning', [
                'status' => !empty($analytics) ? 'Analytics found' : 'No analytics found',
                'analytics_found' => $analytics
            ]);

        } catch (\Exception $e) {
            $this->addError('analytics', $e->getMessage());
        }
    }

    /**
     * Check security headers
     */
    private function checkSecurity(): void
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl);
            $headers = $response->headers();

            // Handle both array and object headers
            $getHeader = function($headerName) use ($headers) {
                if (is_array($headers)) {
                    return $headers[$headerName] ?? null;
                } else {
                    return $headers->get($headerName);
                }
            };

            $securityHeaders = [
                'X-Frame-Options' => $getHeader('X-Frame-Options'),
                'X-Content-Type-Options' => $getHeader('X-Content-Type-Options'),
                'X-XSS-Protection' => $getHeader('X-XSS-Protection'),
                'Strict-Transport-Security' => $getHeader('Strict-Transport-Security'),
                'Content-Security-Policy' => $getHeader('Content-Security-Policy')
            ];

            $presentHeaders = array_filter($securityHeaders);
            $score = (count($presentHeaders) / count($securityHeaders)) * 100;

            $this->addResult('security', $score >= 80 ? 'good' : ($score >= 40 ? 'warning' : 'critical'), [
                'score' => round($score, 2),
                'headers_present' => array_keys($presentHeaders),
                'headers_missing' => array_keys(array_diff_key($securityHeaders, $presentHeaders))
            ]);

        } catch (\Exception $e) {
            $this->addError('security', $e->getMessage());
        }
    }

    /**
     * Check accessibility
     */
    private function checkAccessibility(): void
    {
        try {
            $html = $this->fetchPageContent();
            $hasAltText = str_contains($html, 'alt=');
            $hasAriaLabels = str_contains($html, 'aria-label') || str_contains($html, 'aria-labelledby');
            $hasSkipLinks = str_contains($html, 'skip') || str_contains($html, 'jump');

            $accessibilityFeatures = [];
            if ($hasAltText) $accessibilityFeatures[] = 'Alt text on images';
            if ($hasAriaLabels) $accessibilityFeatures[] = 'ARIA labels';
            if ($hasSkipLinks) $accessibilityFeatures[] = 'Skip links';

            $score = count($accessibilityFeatures) * 33.33;

            $this->addResult('accessibility', $score >= 80 ? 'good' : ($score >= 40 ? 'warning' : 'critical'), [
                'score' => round($score, 2),
                'features_found' => $accessibilityFeatures
            ]);

        } catch (\Exception $e) {
            $this->addError('accessibility', $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function fetchPageContent(): string
    {
        $cacheKey = 'seo_audit_content_' . md5($this->baseUrl);
        
        return Cache::remember($cacheKey, 300, function () {
            $response = Http::timeout(30)->get($this->baseUrl);
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch page content: ' . $response->status());
            }
            return $response->body();
        });
    }

    private function addResult(string $check, string $status, array $data): void
    {
        $this->auditResults[$check] = [
            'status' => $status,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];
    }

    private function addError(string $check, string $message): void
    {
        $this->errors[$check] = [
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];
    }

    private function generateSummary(): array
    {
        $totalChecks = count($this->auditResults);
        $goodChecks = count(array_filter($this->auditResults, fn($r) => $r['status'] === 'good'));
        $warningChecks = count(array_filter($this->auditResults, fn($r) => $r['status'] === 'warning'));
        $criticalChecks = count(array_filter($this->auditResults, fn($r) => $r['status'] === 'critical'));

        $overallScore = $totalChecks > 0 ? ($goodChecks / $totalChecks) * 100 : 0;

        return [
            'overall_score' => round($overallScore, 2),
            'total_checks' => $totalChecks,
            'good' => $goodChecks,
            'warning' => $warningChecks,
            'critical' => $criticalChecks,
            'errors' => count($this->errors)
        ];
    }

    /**
     * Generate AI summary of audit results
     */
    private function generateAISummary(array $auditResult): string
    {
        try {
            $openAIService = app(\App\Services\OpenAIService::class);
            
            $prompt = "Analyze this SEO audit result and provide a concise summary with actionable recommendations:\n\n" . json_encode($auditResult);
            
            $messages = [
                ['role' => 'user', 'content' => $prompt]
            ];

            $response = $openAIService->chat($messages);

            if ($response['success']) {
                return $response['message'];
            }

            return 'Unable to generate AI summary at this time.';
            
        } catch (\Exception $e) {
            Log::error('Failed to generate AI summary for SEO audit', [
                'error' => $e->getMessage()
            ]);
            
            return 'Unable to generate AI summary at this time.';
        }
    }

    private function countSyllables(string $text): int
    {
        // Simplified syllable counting
        $text = strtolower($text);
        $text = preg_replace('/[^a-z]/', '', $text);
        $syllables = 0;
        $previous = '';
        
        for ($i = 0; $i < strlen($text); $i++) {
            $current = $text[$i];
            if (in_array($current, ['a', 'e', 'i', 'o', 'u', 'y'])) {
                if (!in_array($previous, ['a', 'e', 'i', 'o', 'u', 'y'])) {
                    $syllables++;
                }
            }
            $previous = $current;
        }
        
        return max(1, $syllables);
    }
} 