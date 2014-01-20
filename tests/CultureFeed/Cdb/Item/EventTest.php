<?php

class CultureFeed_Cdb_Item_EventTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CultureFeed_Cdb_Item_Event
     */
    protected $event;

    public function setUp()
    {
        $this->event = new CultureFeed_Cdb_Item_Event();
    }

    /**
     * @param $fileName
     * @return SimpleXMLElement
     */
    public function loadSample($fileName) {
        $sampleDir = __DIR__ . '/samples/EventTest/';
        $filePath = $sampleDir . $fileName;

        $xml = simplexml_load_file($filePath, 'SimpleXMLElement', 0, \CultureFeed_Cdb_Default::CDB_SCHEME_URL);

        return $xml;
    }

  public function samplePath($fileName) {
    $sampleDir = __DIR__ . '/samples/EventTest/';
    $filePath = $sampleDir . $fileName;

    return $filePath;
  }

    public function testAppendsCdbidAttributeOnlyWhenCdbidIsSet()
    {
        $this->assertEquals(NULL, $this->event->getCdbId());

        $dom = new DOMDocument();
        $eventsElement = $dom->createElement('events');
        $dom->appendChild($eventsElement);
        $this->event->appendToDOM($eventsElement);

        $xpath = new DOMXPath($dom);

        $items = $xpath->query('/events/event');
        $this->assertEquals(1, $items->length);

        $items = $xpath->query('/events/event/@cdbid');
        $this->assertEquals(0, $items->length);

        $uuid = '0FA6D598-F126-4B4F-BCE3-BAF3BD959A35';
        $this->event->setCdbId($uuid);
        $this->assertEquals($uuid, $this->event->getCdbId());

        $dom = new DOMDocument();
        $eventsElement = $dom->createElement('events');
        $dom->appendChild($eventsElement);
        $this->event->appendToDOM($eventsElement);

        $xpath = new DOMXPath($dom);

        $items = $xpath->query('/events/event');
        $this->assertEquals(1, $items->length);

        $items = $xpath->query('/events/event/@cdbid');
        $this->assertEquals(1, $items->length);

        $this->assertEquals($uuid, $items->item(0)->textContent);
    }

    /**
     * @return array
     */
    public function privatePropertyValues() {
        return array(
            array(TRUE),
            array(FALSE),
        );
    }

    /**
     * @dataProvider privatePropertyValues
     */
    public function testAppendsBooleanPrivateProperty($value) {
        $this->assertNULL($this->event->isPrivate());

        $dom = new DOMDocument();
        $eventsElement = $dom->createElement('events');
        $dom->appendChild($eventsElement);
        $this->event->appendToDOM($eventsElement);

        $xpath = new DOMXPath($dom);

        $items = $xpath->query('/events/event');
        $this->assertEquals(1, $items->length);

        $items = $xpath->query('/events/event/@private');
        $this->assertEquals(0, $items->length);

        $this->event->setPrivate($value);

        $this->assertInternalType('boolean', $this->event->isPrivate());
        $this->assertEquals($value, $this->event->isPrivate());
    }

    public function privatePropertySamples() {
        return array(
          array('private.xml', TRUE),
          array('non-private.xml', FALSE),
        );
    }

    /**
     * @dataProvider privatePropertySamples
     * @param $sampleName
     * @param $value
     */
    public function testCreateFromXmlParsesPrivateAttribute($sampleName, $value) {
        $xml = $this->loadSample($sampleName);
        //var_dump($xml->asXML());
        $event = CultureFeed_Cdb_Item_Event::parseFromCdbXml($xml);

        $this->assertEquals($value, $event->isPrivate());
    }

    public function testParseCdbXMLGuideExample6Dot2() {
        $xml = $this->loadSample('cdbxml-guide-example-6-2.xml');

        $event = CultureFeed_Cdb_Item_Event::parseFromCdbXml($xml);

        $this->assertInstanceOf('CultureFeed_Cdb_Item_Event', $event);

        $this->assertEquals('ea37cae2-c91e-4810-89ab-e060432d2b78', $event->getCdbId());
        $this->assertEquals('2010-02-25T00:00:00', $event->getAvailableFrom());
        $this->assertEquals('2010-08-09T00:00:00', $event->getAvailableTo());
        $this->assertEquals('mverdoodt', $event->getCreatedBy());
        $this->assertEquals('2010-07-05T18:28:18', $event->getCreationDate());
        $this->assertEquals('SKB Import:SKB00001_216413', $event->getExternalId());
        $this->assertFalse($event->isParent());
        $this->assertEquals('2010-07-28T13:58:55', $event->getLastUpdated());
        $this->assertEquals('mverdoodt', $event->getLastUpdatedBy());
        $this->assertEquals('SKB Import', $event->getOwner());
        $this->assertEquals(80, $event->getPctComplete());
        $this->assertFalse($event->isPrivate());
        $this->assertTrue($event->isPublished());
        $this->assertEquals('SKB', $event->getValidator());
        $this->assertEquals('approved', $event->getWfStatus());

        $this->assertEquals(18, $event->getAgeFrom());

        $calendar = $event->getCalendar();

        $this->assertInstanceOf('CultureFeed_Cdb_Data_Calendar', $calendar);

        $this->assertCount(1, $calendar);

        $calendar->rewind();
        /** @var CultureFeed_Cdb_Data_Calendar_Timestamp $timestamp */
        $timestamp = $calendar->current();

        $this->assertInstanceOf('CultureFeed_Cdb_Data_Calendar_Timestamp', $timestamp);

        $this->assertEquals('2010-08-01', $timestamp->getDate());

        $this->assertEquals('21:00:00.0000000', $timestamp->getStartTime());

        $this->assertNULL($timestamp->getEndTime());

      $categories = $event->getCategories();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_CategoryList', $categories);

      $this->assertCount(3, $categories);
      $this->assertContainsOnly('CultureFeed_Cdb_Data_Category', $categories);

      /** @var CultureFeed_Cdb_Data_Category $category */
      $categories->rewind();
      $category = $categories->current();
      $this->assertEquals('0.50.4.0.0', $category->getId());
      $this->assertEquals('Concert', $category->getName());
      $this->assertEquals('eventtype', $category->getType());

      $categories->next();
      $category = $categories->current();
      $this->assertEquals('1.8.2.0.0', $category->getId());
      $this->assertEquals('Jazz en blues', $category->getName());
      $this->assertEquals('theme', $category->getType());

      $categories->next();
      $category = $categories->current();
      $this->assertEquals('6.2.0.0.0', $category->getId());
      $this->assertEquals('Regionaal', $category->getName());
      $this->assertEquals('publicscope', $category->getType());

      $contact_info = $event->getContactInfo();
      $mails = $contact_info->getMails();
      $this->assertInternalType('array', $mails);
      $this->assertCount(1, $mails);
      $this->assertContainsOnly('CultureFeed_Cdb_Data_Mail', $mails);

      /** @var CultureFeed_Cdb_Data_Mail $mail */
      $mail = reset($mails);
      $this->assertEquals('info@bonnefooi.be', $mail->getMailAddress());
      $this->assertFalse($mail->isForReservations());
      $this->assertFalse($mail->isMainMail());

      $phones = $contact_info->getPhones();
      $this->assertInternalType('array', $phones);
      $this->assertCount(1, $phones);
      $this->assertContainsOnly('CultureFeed_Cdb_Data_Phone', $phones);

      /** @var CultureFeed_Cdb_Data_Phone $phone */
      $phone = reset($phones);
      $this->assertEquals('0487-62.22.31', $phone->getNumber());
      $this->assertFalse($phone->isForReservations());
      $this->assertFalse($phone->isMainPhone());
      $this->assertNULL($phone->getType());

      $urls = $contact_info->getUrls();
      $this->assertInternalType('array', $urls);
      $this->assertCount(1, $urls);
      $this->assertContainsOnly('CultureFeed_Cdb_Data_Url', $urls);

      /** @var CultureFeed_Cdb_Data_Url $url */
      $url = reset($urls);
      $this->assertEquals('http://www.bonnefooi.be', $url->getUrl());
      $this->assertTrue($url->isMain());
      $this->assertFalse($url->isForReservations());

      $event_detail_list = $event->getDetails();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_DetailList', $event_detail_list);
      $this->assertCount(2, $event_detail_list);
      $this->assertContainsOnly('CultureFeed_Cdb_Data_EventDetail', $event_detail_list);

      /** @var CultureFeed_Cdb_Data_EventDetail $detail */
      $event_detail_list->rewind();
      $detail = $event_detail_list->current();
      $this->assertEquals('nl', $detail->getLanguage());

      $this->assertEquals('zo 01/08/10 om 21:00', $detail->getCalendarSummary());

      $performers = $detail->getPerformers();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_PerformerList', $performers);
      $this->assertCount(1, $performers);
      $this->assertContainsOnly('CultureFeed_Cdb_Data_Performer', $performers);

      $performers->rewind();
      /** @var CultureFeed_Cdb_Data_Performer $performer */
      $performer = $performers->current();

      $this->assertEquals('Muzikant', $performer->getRole());
      $this->assertEquals('Matt, the Englishman in Brussels', $performer->getLabel());

      $this->assertEquals('Weggelaten voor leesbaarheid...', $detail->getLongDescription());

      $media = $detail->getMedia();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_Media', $media);
      $this->assertCount(1, $media);

      $media->rewind();
      /** @var CultureFeed_Cdb_Data_File $media_item */
      $media_item = $media->current();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_File', $media_item);
      $this->assertEquals('Bonnefooi', $media_item->getCopyright());
      $this->assertEquals('http://www.bonnefooi.be/images/sized/site/images/uploads/Jeroen_Jamming-453x604.jpg', $media_item->getHLink());
      $this->assertEquals('imageweb', $media_item->getMediaType());
      $this->assertEquals('Jeroen Jamming', $media_item->getTitle());
      $this->assertNull($media_item->getCdbid());
      $this->assertNull($media_item->getChannel());
      $this->assertNull($media_item->getCreationDate());
      $this->assertNull($media_item->getFileName());
      $this->assertNull($media_item->getFileType());
      $this->assertNull($media_item->getPlainText());
      $this->assertNull($media_item->getRelationType());

      $this->assertEquals('The Bonnefooi Acoustic Jam', $detail->getTitle());

      $price = $detail->getPrice();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_Price', $price);
      $this->assertNull($price->getDescription());
      $this->assertEquals('The Bonnefooi Acoustic Jam', $price->getTitle());
      $this->assertEquals(0, $price->getValue());

      $event_detail_list->next();
      $detail = $event_detail_list->current();

      $this->assertEquals('en', $detail->getLanguage());

      // @todo According to the code in Cdb_Data_CultureFeed_Cdb_Item_Event,
      // keywords are delimited by a semicolon, in our xml sample however they seem to be delimited
      // by a comma.
      /*$keywords = $event->getKeywords();
      $this->assertInternalType('array', $keywords);
      $this->assertCount(2, $keywords);
      $this->assertContainsOnly('string', $keywords, TRUE);

      $keyword = reset($keywords);
      $this->assertEquals('Free Jazz', $keyword);

      $keyword = next($keywords);
      $this->assertEquals('Acoustisch', $keyword);*/

      $languages = $event->getLanguages();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_LanguageList', $languages);
      $this->assertCount(2, $languages);
      $languages->rewind();
      /** @var CultureFeed_Cdb_Data_Language $language */
      $language = $languages->current();
      $this->assertEquals('spoken', $language->getType());
      $this->assertEquals('Nederlands', $language->getLanguage());

      $languages->next();
      $language = $languages->current();
      $this->assertEquals('spoken', $language->getType());
      $this->assertEquals('Frans', $language->getLanguage());

      $location = $event->getLocation();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_Location', $location);
      $this->assertNull($location->getActor());
      $this->assertEquals('920e9755-94a0-42c1-8c8c-9d17f693d0be', $location->getCdbid());
      $this->assertEquals('Café Bonnefooi', $location->getLabel());

      $address = $location->getAddress();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_Address', $address);
      $this->assertNull($address->getLabel());
      $physical_address = $address->getPhysicalAddress();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_Address_PhysicalAddress', $physical_address);
      $this->assertEquals('Brussel', $physical_address->getCity());
      $this->assertEquals('BE', $physical_address->getCountry());
      $this->assertNull($physical_address->getGeoInformation());
      $this->assertEquals('8', $physical_address->getHouseNumber());
      $this->assertEquals('Steenstraat', $physical_address->getStreet());
      $this->assertEquals('1000', $physical_address->getZip());

      $this->assertNull($address->getVirtualAddress());

      $organiser = $event->getOrganiser();
      $this->assertInstanceOf('CultureFeed_Cdb_Data_Organiser', $organiser);

      $this->assertEquals('Café Bonnefooi', $organiser->getLabel());

    }

  public function testParseEvent20140108() {
    $xml = $this->loadSample('test-event-2014-01-08.xml');

    $event = CultureFeed_Cdb_Item_Event::parseFromCdbXml($xml);

    $this->assertEquals('d53c2bc9-8f0e-4c9a-8457-77e8b3cab3d1', $event->getCdbId());
    $this->assertEquals('2014-01-08T00:00:00', $event->getAvailableFrom());
    $this->assertEquals('2014-02-21T00:00:00', $event->getAvailableTo());
    $this->assertEquals('jonas@cultuurnet.be', $event->getCreatedBy());
    $this->assertEquals('2014-01-08T09:43:52', $event->getCreationDate());
    $this->assertEquals('CDB:c2156058-9393-4c95-8821-7787170527c0', $event->getExternalId());
    $this->assertEquals('2014-01-08T09:46:41', $event->getLastUpdated());
    $this->assertEquals('jonas@cultuurnet.be', $event->getLastUpdatedBy());
    $this->assertEquals('CultuurNet Validatoren', $event->getOwner());
    $this->assertEquals(95, $event->getPctComplete());
    $this->assertFalse($event->isPrivate());
    $this->assertTrue($event->isPublished());
    $this->assertEquals('UiTdatabank Validatoren', $event->getValidator());
    $this->assertEquals('approved', $event->getWfStatus());
    $this->assertFalse($event->isParent());

    $this->assertEquals(5, $event->getAgeFrom());

    $calendar = $event->getCalendar();
    $this->assertInstanceOf('CultureFeed_Cdb_Data_Calendar_TimestampList', $calendar);
    $this->assertCount(2, $calendar);
    $this->assertContainsOnly('CultureFeed_Cdb_Data_Calendar_Timestamp', $calendar);

    $calendar->rewind();
    /** @var CultureFeed_Cdb_Data_Calendar_Timestamp $timestamp */
    $timestamp = $calendar->current();

    $this->assertEquals('2014-01-31', $timestamp->getDate());
    $this->assertEquals('12:00:00', $timestamp->getStartTime());
    $this->assertEquals('15:00:00', $timestamp->getEndTime());

    $calendar->next();
    $timestamp = $calendar->current();

    $this->assertEquals('2014-02-20', $timestamp->getDate());
    $this->assertEquals('12:00:00', $timestamp->getStartTime());
    $this->assertEquals('15:00:00', $timestamp->getEndTime());

    $categories = $event->getCategories();
    $this->assertInstanceOf('CultureFeed_Cdb_Data_CategoryList', $categories);
    $this->assertCount(6, $categories);

    $categories->rewind();
    /** @var CultureFeed_Cdb_Data_Category $category */
    $category = $categories->current();

    $this->assertEquals('1.7.6.0.0', $category->getId());
    $this->assertEquals('Griezelfilm of horror', $category->getName());
    $this->assertEquals('theme', $category->getType());

    $categories->next();
    $category = $categories->current();
    $this->assertEquals('6.0.0.0.0', $category->getId());
    $this->assertEquals('Wijk of buurt', $category->getName());
    $this->assertEquals('publicscope', $category->getType());

    $categories->next();
    $category = $categories->current();
    $this->assertEquals('2.2.1.0.0', $category->getId());
    $this->assertEquals('Vanaf kleuterleeftijd (3+)', $category->getName());
    $this->assertEquals('targetaudience', $category->getType());

    $categories->next();
    $category = $categories->current();
    $this->assertEquals('0.50.6.0.0', $category->getId());
    $this->assertEquals('Film', $category->getName());
    $this->assertEquals('eventtype', $category->getType());

    $categories->next();
    $category = $categories->current();
    $this->assertEquals('reg.1565', $category->getId());
    $this->assertEquals('1000 Brussel', $category->getName());
    $this->assertEquals('flandersregion', $category->getType());

    $categories->next();
    $category = $categories->current();
    $this->assertEquals('umv.7', $category->getId());
    $this->assertEquals('Film', $category->getName());
    $this->assertEquals('umv', $category->getType());

    $contact_info = $event->getContactInfo();

    $addresses = $contact_info->getAddresses();
    $this->assertInternalType('array', $addresses);
    $this->assertCount(1, $addresses);

    /** @var CultureFeed_Cdb_Data_Address $address */
    $address = reset($addresses);
    $this->assertInstanceOf('CultureFeed_Cdb_Data_Address', $address);

  }

  public function testCreateCdbXMLGuideExample6Dot2() {
    $event = new CultureFeed_Cdb_Item_Event();
    $event->setAvailableFrom('2010-02-25T00:00:00');
    $event->setAvailableTo('2010-08-09T00:00:00');
    $event->setCdbId('ea37cae2-c91e-4810-89ab-e060432d2b78');
    $event->setCreatedBy('mverdoodt');
    $event->setCreationDate('2010-07-05T18:28:18');
    $event->setExternalId('SKB Import:SKB00001_216413');
    $event->setIsParent(FALSE);
    $event->setLastUpdated('2010-07-28T13:58:55');
    $event->setLastUpdatedBy('mverdoodt');
    $event->setOwner('SKB Import');
    $event->setPctComplete(80);
    $event->setPublished(TRUE);
    $event->setValidator('SKB');
    $event->setWfStatus('approved');
    $event->setAgeFrom(18);
    $event->setPrivate(FALSE);

    $calendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
    $calendar->add(new CultureFeed_Cdb_Data_Calendar_Timestamp('2010-08-01', '21:00:00.0000000'));
    $event->setCalendar($calendar);

    $categories = new CultureFeed_Cdb_Data_CategoryList();
    $categories->add(new CultureFeed_Cdb_Data_Category(CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_EVENT_TYPE, '0.50.4.0.0', 'Concert'));
    $categories->add(new CultureFeed_Cdb_Data_Category(CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_THEME, '1.8.2.0.0', 'Jazz en blues'));
    $categories->add(new CultureFeed_Cdb_Data_Category(CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_PUBLICSCOPE, '6.2.0.0.0', 'Regionaal'));
    $event->setCategories($categories);

    $contactInfo = new CultureFeed_Cdb_Data_ContactInfo();
    $contactInfo->addMail(new CultureFeed_Cdb_Data_Mail('info@bonnefooi.be', NULL, NULL));
    $contactInfo->addPhone(new CultureFeed_Cdb_Data_Phone('0487-62.22.31'));
    $url = new CultureFeed_Cdb_Data_Url('http://www.bonnefooi.be');
    $url->setMain();
    $contactInfo->addUrl($url);
    $event->setContactInfo($contactInfo);

    $details = new CultureFeed_Cdb_Data_EventDetailList();

    $detailNl = new CultureFeed_Cdb_Data_EventDetail();
    $detailNl->setLanguage('nl');
    $detailNl->setTitle('The Bonnefooi Acoustic Jam');
    $detailNl->setCalendarSummary('zo 01/08/10 om 21:00');

    $performers = new CultureFeed_Cdb_Data_PerformerList();
    $performers->add(new CultureFeed_Cdb_Data_Performer('Muzikant', 'Matt, the Englishman in Brussels'));
    $detailNl->setPerformers($performers);

    $detailNl->setLongDescription('Weggelaten voor leesbaarheid...');

    $file = new CultureFeed_Cdb_Data_File();
    $file->setMain();
    $file->setCopyright('Bonnefooi');
    $file->setHLink('http://www.bonnefooi.be/images/sized/site/images/uploads/Jeroen_Jamming-453x604.jpg');
    $file->setMediaType(CultureFeed_Cdb_Data_File::MEDIA_TYPE_IMAGEWEB);
    $file->setTitle('Jeroen Jamming');

    $detailNl->getMedia()->add($file);

    $price = new CultureFeed_Cdb_Data_Price(0);
    $price->setTitle('The Bonnefooi Acoustic Jam');
    $detailNl->setPrice($price);

    $detailNl->setShortDescription('Korte omschrijving.');

    $details->add($detailNl);

    $detailEn = new CultureFeed_Cdb_Data_EventDetail();
    $detailEn->setLanguage('en');
    $detailEn->setShortDescription('Short description.');
    $details->add($detailEn);

    $event->setDetails($details);

    // @todo Add headings.
    //$headings = array();

    $event->addKeyword('Free Jazz, Acoustisch');


    $address = new CultureFeed_Cdb_Data_Address();
    $physicalAddress = new CultureFeed_Cdb_Data_Address_PhysicalAddress();
    $physicalAddress->setCity('Brussel');
    $physicalAddress->setCountry('BE');
    $physicalAddress->setHouseNumber(8);
    $physicalAddress->setStreet('Steenstraat');
    $physicalAddress->setZip(1000);
    $address->setPhysicalAddress($physicalAddress);

    $location = new CultureFeed_Cdb_Data_Location($address);

    $location->setLabel('Café Bonnefooi');
    $location->setCdbid('920e9755-94a0-42c1-8c8c-9d17f693d0be');
    $event->setLocation($location);

    $organiser = new CultureFeed_Cdb_Data_Organiser();
    $organiser->setLabel('Café Bonnefooi');
    $event->setOrganiser($organiser);

    $languages = new CultureFeed_Cdb_Data_LanguageList();
    $languages->add(new CultureFeed_Cdb_Data_Language('Nederlands', CultureFeed_Cdb_Data_Language::TYPE_SPOKEN));
    $languages->add(new CultureFeed_Cdb_Data_Language('Frans', CultureFeed_Cdb_Data_Language::TYPE_SPOKEN));
    $event->setLanguages($languages);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = FALSE;
    $dom->formatOutput = TRUE;
    $dummy_element = $dom->createElementNS(CultureFeed_Cdb_Xml::namespaceUri(), 'cdbxml');

    $dom->appendChild($dummy_element);

    $event->appendToDOM($dummy_element);

    $xpath = new DOMXPath($dom);

    $items = $xpath->query('//event');
    $this->assertEquals(1, $items->length);

    $event_element = $items->item(0);

    $dom->removeChild($dummy_element);
    $dom->appendChild($event_element);
    /*$namespaceAttribute = $dom->createAttribute('xmlns');
    $namespaceAttribute->value = CultureFeed_Cdb_Xml::namespaceUri();
    $event_element->appendChild($namespaceAttribute);*/

    // @todo Put xmlns attribute first.

    $xml = $dom->saveXML();

    $sample_dom = new DOMDocument('1.0', 'UTF-8');
    $contents = file_get_contents($this->samplePath('cdbxml-guide-example-6-2.xml'));
    $contents = str_replace('xmlns="http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL" ', '', $contents);
    $sample_dom->preserveWhiteSpace = FALSE;
    $sample_dom->formatOutput = TRUE;
    $sample_dom->loadXML($contents);
    $sample_dom->preserveWhiteSpace = FALSE;
    $sample_dom->formatOutput = TRUE;

    $expected_xml = $sample_dom->saveXML();
    //$this->assertEquals($sample_dom->documentElement->C14N(), $dom->documentElement->C14N());
    $this->assertEquals($expected_xml, $xml);
  }

  /**
   * Asserts that two XML documents are equal.
   *
   * @param  string $expectedFile
   * @param  string $actualXml
   * @param  string $message
   * @since  Method available since Release 3.3.0
   */
  public static function assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message = '')
  {
    self::assertFileExists($expectedFile);

    $expected = new DOMDocument('1.0', 'utf-8');
    $expected->preserveWhiteSpace = FALSE;
    $expected->load($expectedFile);
    $expected->preserveWhiteSpace = FALSE;
    $expected->formatOutput = FALSE;

    $actual = new DOMDocument('1.0', 'utf-8');
    $actual->preserveWhiteSpace = FALSE;
    $actual->loadXML($actualXml);
    $actual->preserveWhiteSpace = FALSE;
    $actual->formatOutput = FALSE;

    self::assertEquals($expected, $actual, $message);
  }
}
