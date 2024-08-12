<?php

namespace App\Utilities;

use SimonHamp\TheOg\BorderPosition;
use SimonHamp\TheOg\Layout\AbstractLayout;
use SimonHamp\TheOg\Layout\PictureBox;
use SimonHamp\TheOg\Layout\Position;
use SimonHamp\TheOg\Layout\TextBox;

class OpengraphImageLayout extends AbstractLayout
{
    protected BorderPosition $borderPosition = BorderPosition::Bottom;

    protected int $borderWidth = 25;

    protected int $height = 640;

    protected int $padding = 40;

    protected int $width = 1280;

    public function features(): void
    {
        $this->addFeature((new TextBox())
            ->name('title-shadow')
            ->text($this->title())
            ->color($this->config->theme->getUrlColor())
            ->font(new OpengraphOstrichSansRoundedFont())
            ->size(60)
            ->vAlign('center')
            ->hAlign('center')
            ->box($this->mountArea()->box->width(), 600)
            ->position(
                x: 642,
                y: 302,
            )
        );

        $this->addFeature((new TextBox())
            ->name('title')
            ->text($this->title())
            ->color($this->config->theme->getTitleColor())
            ->font(new OpengraphOstrichSansRoundedFont())
            ->size(60)
            ->vAlign('center')
            ->hAlign('center')
            ->box($this->mountArea()->box->width(), 600)
            ->position(
                x: 640,
                y: 300,
            )
        );

        if ($description = $this->description()) {
            $this->addFeature((new TextBox())
                ->name('description')
                ->text($description)
                ->color($this->config->theme->getDescriptionColor())
                ->font($this->config->theme->getDescriptionFont())
                ->size(28)
                ->vAlign('center')
                ->hAlign('center')
                ->box($this->mountArea()->box->width(), 240)
                ->position(
                    x: 0,
                    y: 80,
                    relativeTo: fn() => $this->getFeature('title')->anchor(Position::BottomLeft),
                )
            );
        }

        if ($url = $this->url()) {
            $this->addFeature((new TextBox())
                ->name('url')
                ->text($url)
                ->color($this->config->theme->getUrlColor())
                ->font($this->config->theme->getUrlFont())
                ->size(28)
                ->box($this->mountArea()->box->width(), 600)
                ->position(
                    x: 640,
                    y: 600,
                    anchor: Position::MiddleBottom,
                )
            );
        }
    }
}
