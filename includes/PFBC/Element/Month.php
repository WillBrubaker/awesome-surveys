<?php
class Element_Month extends Element_Textbox {
    protected $_attributes = array(
        "type" => "month",
        "pattern" => "\d{4}-\d{2}"
    );

    public function __construct($label, $name, array $properties = null) {
        $this->_attributes["placeholder"] = "YYYY-MM (e.g. " . date("Y-m") . ")";
        $this->_attributes["title"] = $this->_attributes["placeholder"];

        parent::__construct($label, $name, $properties);
    }

    public function render() {
        parent::render();
    }
}
