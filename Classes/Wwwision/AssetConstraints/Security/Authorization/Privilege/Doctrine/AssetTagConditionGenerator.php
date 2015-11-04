<?php
namespace Wwwision\AssetConstraints\Security\Authorization\Privilege\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Filter\SQLFilter as DoctrineSqlFilter;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Authorization\Privilege\Entity\Doctrine\SqlGeneratorInterface;

/**
 * Condition generator covering Asset <-> Tag relations (M:M relations are not supported by the Flow PropertyConditionGenerator yet)
 */
class AssetTagConditionGenerator implements SqlGeneratorInterface
{

    /**
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $tagLabel;

    /**
     * @param string $tagLabel
     */
    public function __construct($tagLabel)
    {
        $this->tagLabel = $tagLabel;
    }

    /**
     * @param DoctrineSqlFilter $sqlFilter
     * @param ClassMetadata $targetEntity Metadata object for the target entity to create the constraint for
     * @param string $targetTableAlias The target table alias used in the current query
     * @return string
     */
    public function getSql(DoctrineSqlFilter $sqlFilter, ClassMetadata $targetEntity, $targetTableAlias)
    {
        $quotedTagLabel = $this->entityManager->getConnection()->quote($this->tagLabel);
        return $targetTableAlias . '.persistence_object_identifier IN (
            SELECT ' . $targetTableAlias . '_a.persistence_object_identifier
            FROM typo3_media_domain_model_asset AS ' . $targetTableAlias . '_a
            LEFT JOIN typo3_media_domain_model_asset_tags_join ' . $targetTableAlias . '_atj ON ' . $targetTableAlias . '_a.persistence_object_identifier = ' . $targetTableAlias . '_atj.media_asset
            LEFT JOIN typo3_media_domain_model_tag ' . $targetTableAlias . '_t ON ' . $targetTableAlias . '_t.persistence_object_identifier = ' . $targetTableAlias . '_atj.media_tag
            WHERE ' . $targetTableAlias . '_t.label = ' . $quotedTagLabel . ')';
    }
}