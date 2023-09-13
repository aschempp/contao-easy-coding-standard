<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class NoExpectsThisAnyFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The ->expects($this->any()) assertion is the default and can be removed.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        use PHPUnit\Framework\TestCase;

                        class SomeTest extends TestCase
                        {
                            public function testFoo(): void
                            {
                                $mock = $this->createMock(Foo::class);
                                $mock
                                    ->expects($this->any())
                                    ->method("isBar")
                                    ->willReturn(false);
                        }
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_OBJECT_OPERATOR);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_OBJECT_OPERATOR)) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            if (
                'expects' !== $tokens[$nextMeaningful]->getContent()
                || !$tokens[$nextMeaningful + 1]->equals('(')
            ) {
                continue;
            }

            $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextMeaningful + 1);

            if ('($this->any())' !== $tokens->generatePartialCode($nextMeaningful + 1, $end)) {
                continue;
            }

            if ($tokens[$end + 1]->isGivenKind(T_WHITESPACE)) {
                ++$end;
            }

            $tokens->clearRange($index, $end);
        }
    }
}
