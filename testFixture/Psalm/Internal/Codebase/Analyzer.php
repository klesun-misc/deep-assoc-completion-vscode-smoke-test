<?php
namespace Psalm\Internal\Codebase;

/**
 * @psalm-type  TaggedCodeType = array<int, array{0: int, 1: string}>
 *
 * @psalm-type  FileMapType = array{
 *      0: TaggedCodeType,
 *      1: TaggedCodeType,
 *      2: array<int, array{0: int, 1: string, 2: int}>
 * }
 */

 /**
 * @internal
 *
 * Called in the analysis phase of Psalm's execution
 */
class Analyzer
{
}