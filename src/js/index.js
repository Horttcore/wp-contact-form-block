/**
 * Internal block libraries
 */
const el = wp.element.createElement;
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { ServerSideRender } = wp.components;

registerBlockType("horttcore/contact-form", {
  title: __("Contact form", "wp-contact-form-block"),
  icon: "email-alt",
  keywords: [
    __("Contact", "wp-contact-form-block"),
    __("Form", "wp-contact-form-block")
  ],
  category: "widgets",
  edit: function(props) {
    return el(ServerSideRender, {
      block: "horttcore/contact-form"
    });
  },
  save() {
    // Rendering in PHP
    return;
  }
});
