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
        $titleCharacterLength = strlen($this->title());

        $titleSize = match (true) {
            $titleCharacterLength >= 80 => 40,
            $titleCharacterLength >= 60 => 50,
            $titleCharacterLength >= 40 => 60,
            $titleCharacterLength >= 30 => 70,
            default => 80,
        };

        for ($i = -1; $i <= 1; $i++) {
            for ($j = -1; $j <= 1; $j++) {
                $this->addFeature((new TextBox())
                    ->name("title-shadow-{$i}-{$j}")
                    ->text($this->title())
                    ->color($this->config->theme->getUrlColor())
                    ->font(new OpengraphOstrichSansRoundedFont())
                    ->size($titleSize)
                    ->hAlign('center')
                    ->box(600, 600)
                    ->position(
                        x: 642 + $i,
                        y: 302 + $j,
                    )
                );
            }
        }

        for ($i = -1; $i <= 1; $i++) {
            for ($j = -1; $j <= 1; $j++) {
                $this->addFeature((new TextBox())
                    ->name("title-{$i}-{$j}")
                    ->text($this->title())
                    ->color($this->config->theme->getTitleColor())
                    ->font(new OpengraphOstrichSansRoundedFont())
                    ->size($titleSize)
                    ->hAlign('center')
                    ->box(600, 600)
                    ->position(
                        x: 640 + $i,
                        y: 300 + $j,
                    )
                );
            }
        }

        if ($description = $this->description()) {
            $this->addFeature((new TextBox())
                ->name('description')
                ->text($description)
                ->color($this->config->theme->getDescriptionColor())
                ->font($this->config->theme->getDescriptionFont())
                ->size(40)
                ->vAlign('center')
                ->hAlign('center')
                ->box($this->mountArea()->box->width(), 240)
                ->position(
                    x: 0,
                    y: 80,
                    relativeTo: fn() => $this->getFeature('title-0-0')->anchor(Position::BottomLeft),
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
