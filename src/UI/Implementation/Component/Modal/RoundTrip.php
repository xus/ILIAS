<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTrip extends Modal implements Component\Modal\RoundTrip {

	/**
	 * @var Button\Button[]
	 */
	protected $action_buttons = array();

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var Component\Component[]
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $cancel_button_label = 'cancel';

	protected $ajax_content_url;
	protected $replace_content_signal;


	/**
	 * @param string $title
	 * @param Component\Component|Component\Component[] $content
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct($title, $content, SignalGeneratorInterface $signal_generator) {
		parent::__construct($signal_generator);
		$this->checkStringArg('title', $title);
		$content = $this->toArray($content);
		$types = array(Component\Component::class);
		$this->checkArgListElements('content', $content, $types);
		$this->title = $title;
		$this->content = $content;
	}


	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @inheritdoc
	 */
	public function getContent() {
		return $this->content;
	}


	/**
	 * @inheritdoc
	 */
	public function getActionButtons() {
		return $this->action_buttons;
	}


	/**
	 * @inheritdoc
	 */
	public function withActionButtons(array $buttons) {
		$types = array(Button\Button::class);
		$this->checkArgListElements('buttons', $buttons, $types);
		$clone = clone $this;
		$clone->action_buttons = $buttons;
		return $clone;
	}


	/**
	 * @inheritdoc
	 */
	public function getCancelButtonLabel() {
		return $this->cancel_button_label;
	}

	/**
	 * @param string $label
	 * @return RoundTrip
	 */
	public function withCancelButtonLabel($label) {
		$clone = clone $this;
		$clone->cancel_button_label = $label;
		return $clone;
	}


	/**
	 *
	 *
	 *
	 *
	 *
	 * WORKING HERE
	 *
	 *
	 *
	 *
	 *
	 *
	 */
	/**
	 * @inheritdoc
	 */
	public function withAsyncContentUrl($url) {
		$this->checkStringArg('url', $url);
		$clone = clone $this;
		$clone->ajax_content_url = $url;

		return $clone;
	}

	public function getAsyncContentUrl()
	{
		//remove this dummy line
		return $this->ajax_content_url;
	}

	/**
	 * @inheritdoc
	 */
	public function getReplaceContentSignal() {
		return $this->replace_content_signal;
	}

	/**
	 * Set the show/close/replace signals for this modal
	 */
	protected function initSignals() {
		parent::initSignals();
		//signal generator from parent class
		$this->replace_content_signal = $this->signal_generator->create("ILIAS\\UI\\Implementation\\Component\\Modal\\ReplaceContentSignal");
	}
}
