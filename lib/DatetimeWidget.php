<?php

class DatetimeWidget extends SidebarWidget
{

    public function __construct($url, $name, $placeholder = null)
    {
        parent::__construct();

        $this->url         = $url ?: $_SERVER['REQUEST_URI'];
        $this->title       = _('Datum-Filter');
        $this->name        = $name;
        $this->placeholder = $placeholder;
        $this->template    = 'sidebar/datetime-widget';
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function render($variables = [])
    {
        $tf = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $tf->open($this->template);

        $template->set_attributes($variables + $this->template_variables);
        $template->elements = $this->elements;

        if ($this->layout) {
            $layout = $GLOBALS['template_factory']->open($this->layout);
            $layout->layout_css_classes = $this->layout_css_classes;
            $layout->additional_attributes = [];
            $template->set_layout($layout);
        }

        return $template->render();
    }
}
