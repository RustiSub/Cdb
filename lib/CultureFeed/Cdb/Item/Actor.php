<?php

/**
 * @class
 * Representation of an actor on the culturefeed.
 */
class CultureFeed_Cdb_Item_Actor extends CultureFeed_Cdb_Item_Base implements CultureFeed_Cdb_IElement {

    /**
     * Is the actor object centrally guarded for uniqueness
     * @var bool
     */
    protected $asset;

    /**
     * @var CultureFeed_Cdb_Data_ActorDetailList
     */
    protected $details;

    /**
     * Contact info for an actor.
     *
     * @var CultureFeed_Cdb_Data_ContactInfo
     */
    protected $contactInfo;

    /**
     * Week scheme for the opening times from the actor.
     * @var CultureFeed_Cdb_Data_Calendar_WeekScheme
     */
    protected $weekScheme;

    /**
     * @var string
     */
    protected $availableFrom;

    /**
     * @var string
     */
    protected $availableTo;

    /**
     * @var string
     */
    protected $createdBy;

    /**
     * @var string
     */
    protected $creationDate;

    /**
     * @var string
     */
    protected $owner;

    /**
     * Construct the actor.
     */
    public function __construct() {
        $this->details = new CultureFeed_Cdb_Data_ActorDetailList();
    }

    /**
     * Get the contact info of this actor.
     */
    public function getContactInfo() {
        return $this->contactInfo;
    }

    /**
     * Get the weekscheme of this actor.
     */
    public function getWeekScheme() {
        return $this->weekScheme;
    }

    /**
     * Set the contact info of this contact.
     * @param CultureFeed_Cdb_Data_Calendar $contactInfo
     *   Contact info to set.
     */
    public function setContactInfo(CultureFeed_Cdb_Data_ContactInfo $contactInfo) {
        $this->contactInfo = $contactInfo;
    }

    /**
     * Get the weekscheme of this actor.
     */
    public function setWeekScheme(CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme) {
        $this->weekScheme = $weekScheme;
    }

    public function setAvailableFrom($value) {
        $this->availableFrom = $value;
    }

    public function getAvailableFrom() {
        return $this->availableFrom;
    }

    public function setAvailableTo($value) {
        $this->availableTo = $value;
    }

    public function getAvailableTo() {
        return $this->availableTo;
    }

    public function setCreatedBy($author) {
        $this->createdBy = $author;
    }

    public function getCreatedBy() {
        return $this->createdBy;
    }

    public function setCreationDate($value) {
        $this->creationDate = $value;
    }

    public function getCreationDate() {
        return $this->creationDate;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function setOwner($owner) {
        $this->owner = $owner;
    }

    /**
     * Appends the current object to the passed DOM tree.
     *
     * @param DOMElement $element
     *   The DOM tree to append to.
     * @param string $cdbScheme
     *   The cdb schema version.
     *
     * @see CultureFeed_Cdb_IElement::appendToDOM()
     */
    public function appendToDOM(DOMElement $element, $cdbScheme = '3.2') {

        $dom = $element->ownerDocument;

        $actorElement = $dom->createElement('actor');

        if ($this->cdbId) {
            $actorElement->setAttribute('cdbid', $this->cdbId);
        }

        if ($this->externalId) {
            $actorElement->setAttribute('externalid', $this->externalId);
        }

        if ($this->details) {
            $this->details->appendToDOM($actorElement);
        }

        if ($this->categories) {
            $this->categories->appendToDOM($actorElement);
        }

        if ($this->contactInfo) {
            $this->contactInfo->appendToDOM($actorElement);
        }

        if (count($this->keywords) > 0) {
            $keywordElement = $dom->createElement('keywords');
            $keywordElement->appendChild($dom->createTextNode(implode(';', $this->keywords)));
            $actorElement->appendChild($keywordElement);
        }

        if ($this->weekScheme) {
            $this->weekScheme->appendToDOM($actorElement);
        }

        $element->appendChild($actorElement);

    }

    /**
     * @see CultureFeed_Cdb_IElement::parseFromCdbXml(SimpleXMLElement $xmlElement)
     * @return CultureFeed_Cdb_Item_Actor
     */
    public static function parseFromCdbXml(SimpleXMLElement $xmlElement) {

        if (empty($xmlElement->categories)) {
            throw new CultureFeed_Cdb_ParseException('Categories missing for actor element');
        }

        if (empty($xmlElement->actordetails)) {
            throw new CultureFeed_Cdb_ParseException('Actordetails missing for actor element');
        }

        $actor = new self();

        $actor_attributes = $xmlElement->attributes();

        if (isset($actor_attributes['cdbid'])) {
            $actor->setCdbId((string)$actor_attributes['cdbid']);
        }

        if (isset($actor_attributes['externalid'])) {
            $actor->setExternalId((string)$actor_attributes['externalid']);
        }

        if (isset($actor_attributes['availablefrom'])) {
            $actor->setAvailableFrom((string)$actor_attributes['availablefrom']);
        }

        if (isset($actor_attributes['availableto'])) {
            $actor->setAvailableTo((string)$actor_attributes['availableto']);
        }

        if (isset($actor_attributes['createdby'])) {
            $actor->setCreatedBy((string)$actor_attributes['createdby']);
        }

        if (isset($actor_attributes['creationdate'])) {
            $actor->setCreationDate((string)$actor_attributes['creationdate']);
        }

        if (isset($actor_attributes['lastupdated'])) {
            $actor->setLastUpdated((string)$actor_attributes['lastupdated']);
        }

        if (isset($actor_attributes['lastupdatedby'])) {
            $actor->setLastUpdatedBy((string)$actor_attributes['lastupdatedby']);
        }

        if (isset($actor_attributes['owner'])) {
            $actor->setOwner((string)$actor_attributes['owner']);
        }

        $actor->setDetails(CultureFeed_Cdb_Data_ActorDetailList::parseFromCdbXml($xmlElement->actordetails));

        // Set categories
        $actor->setCategories(CultureFeed_Cdb_Data_CategoryList::parseFromCdbXml($xmlElement->categories));

        // Set contact information.
        if (!empty($xmlElement->contactinfo)) {
            $actor->setContactInfo(CultureFeed_Cdb_Data_ContactInfo::parseFromCdbXml($xmlElement->contactinfo));
        }

        // Set the keywords.
        self::parseKeywords($xmlElement, $actor);

        // Set the weekscheme.
        if (!empty($xmlElement->weekscheme)) {
            $actor->setWeekScheme(CultureFeed_Cdb_Data_Calendar_Weekscheme::parseFromCdbXml($xmlElement->weekscheme));
        }

        return $actor;
    }
}
