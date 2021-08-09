<?php

namespace bingMap;

//use bingMap\MapPosition;
//use bingMap\HelperMethods;
use SilverStripe\View\ViewableData;

class InfoBox
{
    //use MapPosition;
    
    private $ID;
    private $Title = null;
    private $Description = null;
    private $InitialVisibility = false;
    private $HTMLContent = null;
    private static $Suffix = "InfoBox";

    public function __construct()
    {
        $InitialVisibility = false;
    }
    public static function create()
    {
        return new InfoBox();
    }
    //Will be set by Marker to make JS code more readable
    public function SetID($ID)
    {
        $this->ID = $ID;
        return $this;
    }
    public function SetTitle($Title)
    {
        $this->Title = $Title;
        return $this;
    }
    public function SetDescription($Description)
    {
        $this->Description = $Description;
        return $this;
    }
    public function SetHTMLContent($HTMLContent)
    {
        $this->HTMLContent = $HTMLContent;
        return $this;
    }
    public function SetInitialVisibility($InitialVisibility)
    {
        $this->InitialVisibility = $InitialVisibility;
        return $this;
    }
    public function hasID($ID)
    {
        return $this->ID == null;
    }
    
    private function RenderTitle()
    {
        if($this->Title != null)
        {
            return "title: '{$this->Title}',";
        }
        return "";
    }
    private function RenderDescription()
    {
        if($this->Description != null)
        {
            return "description: '{$this->Description}',";
        }
        return "";
    }
    private function RenderHTMLContent()
    {
        if($this->HTMLContent != null)
        {
            $rendered = $this->getRenderedHTMLContent();
            return "htmlContent: '{$rendered}',";
        }
        return "";
    }
    private function getRenderedHTMLContent()
    {
        if($this->HTMLContent != null)
        {
            $renderer = new ViewableData();
            $rendered = $renderer->customise([
                "HTMLContent"   => $this->HTMLContent,
                "Title" =>  $this->Title,
                "ID"    =>  $this->ID
            ])->renderWith("HTMLInfoBox");
            $rendered = HelperMethods::prepareJavascriptString($rendered);
            return $rendered;
        }
        return "";
    }
    public function RenderHTMLCloser()
    {
        if($this->HTMLContent != null)
        {
            return "function closeInfobox$this->ID(){
                infobox$this->ID.setOptions({visible:false});
            }";
        }
        return "";
    }
    private function RenderInitialVisibility()
    {
        if($this->InitialVisibility == true)
        {
            return "visible: true";
        }
        return "visible: false";
    }
    public function Render($mapVariable, $pushpinVariable)
    {
        if($this->IsValidCoordinate())
        {
            $rendered = "";
            $rendered .= $this->RenderLocationVariable($this->ID,self::$Suffix);
            $rendered .= "
            var infobox$this->ID = new Microsoft.Maps.Infobox(\n
                {$this->GetLocationVariable($this->ID,self::$Suffix)},{\n
                    {$this->RenderTitle()}\n
                    {$this->RenderDescription()}\n
                    {$this->RenderHTMLContent()}\n
                    {$this->RenderInitialVisibility()}\n
                }\n
            );\n
            InfoBoxCollection.push(infobox$this->ID);
            infobox{$this->ID}.setMap($mapVariable);\n
            Microsoft.Maps.Events.addHandler($pushpinVariable,'click',() => {\n
                infobox{$this->ID}.setOptions({visible:true});\n
            });\n
            ";
            
            return $rendered;
        }
        return "console.log('Skipping Invalid Coordinates');\n";
        
    }
    public function GetReactData()
    {
        //we don't want to return false data that has no position to prevent map from not working at all
        if(!$this->IsValidCoordinate())
        {
            return null;
        }
        return [
            "key" => $this->ID,
            "title" => $this->Title,
            "description" => $this->Description,
            "initialVisibility" => $this->InitialVisibility,
            "htmlContent"   =>  $this->getRenderedHTMLContent(),
            "coordinates"   => $this->GetPosition()->GetReactData()
        ];
    }
    
        protected $Coords;

    public function SetPosition($coords)
    {
        $this->Coords = $coords;
        return $this;
    }
    public function GetPosition()
    {
        return $this->Coords;
    }
    public function GetLatitude()
    {
        return $this->Coords->GetLatitude();
    }
    public function GetLongitude()
    {
        return $this->Coords->GetLongitude();
    }
    public function GetLocationVariable($ID,$Suffix)
    {
        return "Location_{$Suffix}_{$ID}";
    }
    public function HasPosition()
    {
        return $this->Coords != null;
    }
    //Might Rename to IsValidCoordinate could cause misunderstanding
    public function IsValidCoordinate()
    {
        return $this->Coords->IsValid();
    }
    public function RenderLocationVariable($ID,$Suffix)
    {
        return "var Location_{$Suffix}_{$ID} = new Microsoft.Maps.Location({$this->GetLatitude()},{$this->GetLongitude()}); ";
    }
    public function RenderLocation()
    {
        if($this->HasPosition())
        {
            return "new Microsoft.Maps.Location({$this->GetLatitude()},{$this->GetLongitude()})";
        }
        return "";
    } 
}
