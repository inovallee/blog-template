<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['category'], name: 'idx_category')]
#[ORM\Index(columns: ['published_at'], name: 'idx_published_at')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(length: 20)]
    private string $category = 'evergreen';

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\Column(length: 70, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(length: 170, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $keyword = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $sources = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $articleCitations = null;

    #[ORM\Column(nullable: true)]
    private ?int $seoScore = null;

    // === SEO / social enrichment (Phase 1) ===
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $ogImageUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $ogImageWidth = null;

    #[ORM\Column(nullable: true)]
    private ?int $ogImageHeight = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $authorName = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DECIMAL, precision: 9, scale: 6, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DECIMAL, precision: 9, scale: 6, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $videoEmbedCode = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $videoThumbnail = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deployedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $t): static { $this->title = $t; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $s): static { $this->slug = $s; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $c): static { $this->content = $c; return $this; }
    public function getExcerpt(): ?string { return $this->excerpt; }
    public function setExcerpt(?string $e): static { $this->excerpt = $e; return $this; }
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $c): static { $this->category = $c; return $this; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $u): static { $this->imageUrl = $u; return $this; }
    public function getImageAlt(): ?string { return $this->imageAlt; }
    public function setImageAlt(?string $a): static { $this->imageAlt = $a; return $this; }
    public function getMetaTitle(): ?string { return $this->metaTitle; }
    public function setMetaTitle(?string $m): static { $this->metaTitle = $m; return $this; }
    public function getMetaDescription(): ?string { return $this->metaDescription; }
    public function setMetaDescription(?string $m): static { $this->metaDescription = $m; return $this; }
    public function getKeyword(): ?string { return $this->keyword; }
    public function setKeyword(?string $k): static { $this->keyword = $k; return $this; }
    public function getTags(): ?array { return $this->tags; }
    public function setTags(?array $t): static { $this->tags = $t; return $this; }
    public function getSources(): ?array { return $this->sources; }
    public function setSources(?array $s): static { $this->sources = $s; return $this; }
    public function getArticleCitations(): ?array { return $this->articleCitations; }
    public function setArticleCitations(?array $c): static { $this->articleCitations = $c; return $this; }
    public function getSeoScore(): ?int { return $this->seoScore; }
    public function setSeoScore(?int $s): static { $this->seoScore = $s; return $this; }
    public function getPublishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?\DateTimeImmutable $p): static { $this->publishedAt = $p; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $c): static { $this->createdAt = $c; return $this; }
    public function getDeployedAt(): ?\DateTimeImmutable { return $this->deployedAt; }
    public function setDeployedAt(?\DateTimeImmutable $d): static { $this->deployedAt = $d; return $this; }

    // === SEO enrichment getters/setters ===
    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $c): static { $this->city = $c; return $this; }
    public function getOgImageUrl(): ?string { return $this->ogImageUrl; }
    public function setOgImageUrl(?string $u): static { $this->ogImageUrl = $u; return $this; }
    public function getOgImageWidth(): ?int { return $this->ogImageWidth; }
    public function setOgImageWidth(?int $w): static { $this->ogImageWidth = $w; return $this; }
    public function getOgImageHeight(): ?int { return $this->ogImageHeight; }
    public function setOgImageHeight(?int $h): static { $this->ogImageHeight = $h; return $this; }
    public function getAuthorName(): ?string { return $this->authorName; }
    public function setAuthorName(?string $a): static { $this->authorName = $a; return $this; }
    public function getLatitude(): ?float { return $this->latitude !== null ? (float) $this->latitude : null; }
    public function setLatitude(?float $lat): static { $this->latitude = $lat !== null ? (string) $lat : null; return $this; }
    public function getLongitude(): ?float { return $this->longitude !== null ? (float) $this->longitude : null; }
    public function setLongitude(?float $lng): static { $this->longitude = $lng !== null ? (string) $lng : null; return $this; }
    public function getVideoUrl(): ?string { return $this->videoUrl; }
    public function setVideoUrl(?string $u): static { $this->videoUrl = $u; return $this; }
    public function getVideoEmbedCode(): ?string { return $this->videoEmbedCode; }
    public function setVideoEmbedCode(?string $c): static { $this->videoEmbedCode = $c; return $this; }
    public function getVideoThumbnail(): ?string { return $this->videoThumbnail; }
    public function setVideoThumbnail(?string $t): static { $this->videoThumbnail = $t; return $this; }
}
