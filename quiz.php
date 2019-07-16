<?php

namespace Grav\Plugin;

use Grav\Common\Page\Interfaces\PageInterface;
use Grav\Common\Page\Types;
use Grav\Common\Plugin;
use Grav\Plugin\Form\Form;
use Grav\Plugin\Form\Forms;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class QuizPlugin
 * @package Grav\Plugin
 */
class QuizPlugin extends Plugin
{
    /**
     * Check if required version is supplied
     *
     * @return bool
     */
    public static function checkRequirements(): bool
    {
        return version_compare(GRAV_VERSION, '1.6', '>');
    }

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => [
                ['onPluginsInitialized', 0]
            ],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0],
                'onGetPageTemplates' => ['onGetPageTemplates', 0],
            ]);
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onPageContentRaw' => ['onPageContentRaw', 0],
            'onTwigPageVariables' => ['onTwigVariables', 0],
            'onTwigSiteVariables' => ['onTwigVariables', 0],
        ]);
    }

    /**
     * Add page template
     *
     * @param Event $event
     */
    public function onGetPageTemplates(Event $event)
    {
        /** @var Types $types */
        $types = $event->types;
        $types->register('quiz');
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onPageContentRaw(Event $e)
    {
        // Get a variable from the plugin configuration
        $text = $this->grav['config']->get('plugins.quiz.text_var');

        // Get the current raw content
        $content = $e['page']->getRawContent();

        // Prepend the output with the custom text and set back on the page
        // $e['page']->setRawContent($text . "\n\n" . $content);
    }

    /**
     * Catches form processing if user posts the form. (Admin)
     */
    public function onPageInitialized()
    {
        /** @var PageInterface $page */
        $page = $this->grav['page'];

        $uri = $this->grav['uri'];
        $nonce = $uri->post('quiz-nonce');
        $status = $nonce ? true : false; // php72 quirk?

        if (!$status) {
            return;
        }

        var_dump($status);
        die ('Saving');
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Make quiz accessible from twig.
     *
     * @param Event $event
     */
    public function onTwigVariables(Event $event = null)
    {
        if ($event && $event['page']) {
            $page = $event['page'];
        } else {
            $page = $this->grav['page'];
        }

        $twig = $this->grav['twig'];

        if (!isset($twig->twig_vars['quiz'])) {
            $settings = $page->header();
            $twig->twig_vars['quiz'] = $settings;
        }
    }
}
