<?php
defined('ABSPATH') or die('No direct script access allowed.');

if (!class_exists('Editorial_Metadata_Input_Paragraph_Handler')) {
    require_once 'editorial-metadata-input-handler.php';

    class Editorial_Metadata_Input_Paragraph_Handler extends Editorial_Metadata_Input_Handler
    {
        /**
         * Class constructor that defines input type.
         *
         * @since   @todo
         */
        public function __construct()
        {
            $this->type = 'paragraph';
        }

        /**
         * Render input html.
         *
         * @access  protected
         * @since   @todo
         *
         * @param   array   $inputOptions   Input options
         * @param   mixed   $value          Actual input value
         */
        protected function renderInput($inputOptions = array(), $value = null)
        {
            $input_name = isset($inputOptions['name']) ? $inputOptions['name'] : '';
            $input_label = isset($inputOptions['label']) ? $inputOptions['label'] : '';
            $input_description = isset($inputOptions['description']) ? $inputOptions['description'] : '';

            self::renderLabel(
                $input_label . self::generateDescriptionHtml($input_description),
                $input_name
            );

            printf(
                '<textarea
                    id="%s"
                    name="%1$s"
                >%2$s</textarea>',
                $input_name,
                $value
            );
        }

        /**
         * Render input-preview html.
         *
         * @access  protected
         * @since   @todo
         *
         * @param   array   $inputOptions   Input options
         * @param   mixed   $value          Actual input value
         */
        protected function renderInputPreview($inputOptions = array(), $value = null)
        {
            $input_name = isset($inputOptions['name']) ? $inputOptions['name'] : '';
            $input_label = isset($inputOptions['label']) ? $inputOptions['label'] : '';
            $input_description = isset($inputOptions['description']) ? $inputOptions['description'] : '';

            self::renderLabel(
                $input_label . self::generateDescriptionHtml($input_description),
                $input_name
            );

            if (mb_strlen((string)$value) > 0) {
                printf(
                    '<span class="pp_editorial_metadata_value">%s</span>',
                    $value
                );
            } else {
                self::renderValuePlaceholder();
            }

            printf(
                '<input
                    type="hidden"
                    id="%s"
                    name="%1$s"
                    value="%2$s"
                />',
                $input_name,
                $value
            );
        }

        /**
         * Get meta-input value html formatted.
         *
         * @access  protected
         * @since   @todo
         *
         * @param   mixed   $value  Actual input value
         *
         * @return  string
         */
        protected function getMetaValueHtml($value = null)
        {
            return !empty($value)
                ? esc_html($value)
                : '';
        }
    }
}
