<?php

class MPDAdapationSet {
    private $asNode;
    private $type;
    private $fileList;
    private $baseUrl;

    public function __construct($asNode, $baseUrl, $lang) {
        $this->fileList = [];
        $this->baseUrl = $baseUrl;
        $this->asNode = $asNode;
        $this->type = $asNode->getAttribute('contentType');
        $repId = $this->asNode->getElementsByTagName('Representation')->item(0)->getAttribute('id');
        $repLang = explode("_",explode("=",$repId)[0])[1];
        if ( $lang != $repLang && $this->type == "audio" ) {
            return;
        }
        $stNodes = $this->asNode->getElementsByTagName('SegmentTemplate');
        foreach($stNodes as $stNode) {
            //$timescale = $stNode->getAttribute('timescale');
            $initialization = $stNode->getAttribute('initialization');
            $media = $stNode->getAttribute('media');
            $sNodes = $stNode->getElementsByTagName('S');
            $timePosition = 0;
            $this->fileList[] = $this->baseUrl.str_replace("\$RepresentationID\$", $repId , $initialization);
            foreach($sNodes as $segNode) {
                $duration = intval($segNode->getAttribute('d'));
                $repeat = intval($segNode->getAttribute('r'));
                if(!$repeat) { $repeat = 1; }
                for($i = 0; $i < $repeat; ++$i) {
                    $filename = str_replace("\$RepresentationID\$", $repId , $media);
                    $filename = $this->baseUrl . str_replace("\$Time\$", $timePosition , $filename);
                    $timePosition += $duration;
                    $this->fileList[] = $filename;
                }
            }
        }
    }
    public function getFileUrls() {
        return $this->fileList;
    }
}

class MPDPeriod {
    private $periodNode;
    private $aSets;

    public function __construct($periodNode, $lang) {
        $this->periodNode = $periodNode;
        $baseUrl = $this->periodNode->getElementsByTagName('BaseURL')->item(0)->textContent;
        $asNodes = $this->periodNode->getElementsByTagName('AdaptationSet');
        $this->aSets = [];
        foreach($asNodes as $asNode) {
            $this->aSets[] = new MPDAdapationSet($asNode, $baseUrl, $lang);
        }
    }

    public function getFileUrls() {
        $retVal = [];
        foreach($this->aSets as $set) {
            foreach($set->getFileUrls() as $setFile) {
                $retVal[] = $setFile;
            }
        }
         return $retVal;
    }

};

class MpegDash {
    private $dom;
    private $periods;

    public function __construct($data, $lang = "fre") {
        $this->dom = new DOMDocument();
        $this->dom->loadXML($data);
        $periodNodes = $this->dom->getElementsByTagName('Period');
        $this->periods = [];
        foreach($periodNodes as $pNode) {
            $this->periods[] = new MPDPeriod($pNode, $lang);
        }
    }

    public function getFileUrls() {
        $retVal = [];
        foreach($this->periods as $period) {
            foreach($period->getFileUrls() as $pFile) {
                $retVal[] = $pFile;
            }
        }
         return $retVal;
    }
}