<?php

namespace Tests\Amtgard\IAM\Fixtures;

/**
 * IDP-shaped JWT policy payloads for claim-composition tests.
 */
final class IdpJwtPolicyFixture
{
    /** Global ORK policy lines issued by the IDP for all integrators. */
    public const GLOBAL_POLICY_LINES = [
        'ORK:1:::::*',
        'ORK:2:::::*',
    ];

    /** Integrator-specific ORK lines (config-driven, IDP-signed). */
    public const INTEGRATOR_POLICY_LINES = [
        'ORK::3::::*',
        'ORK:::::4:*',
    ];

    /** Wrong-prefix line that must not satisfy ORK requirements. */
    public const WRONG_PREFIX_LINE = 'Attendance:1:2:3:4:5:6:ORK/AddAttendance';

    public static function globalOnlyPayload(): array
    {
        return [
            'policy_lines' => self::GLOBAL_POLICY_LINES,
        ];
    }

    public static function globalAndIntegratorPayload(): array
    {
        return [
            'policy_lines' => array_merge(self::GLOBAL_POLICY_LINES, self::INTEGRATOR_POLICY_LINES),
        ];
    }

    public static function globalWithWrongPrefixIntegratorLine(): array
    {
        return [
            'policy_lines' => array_merge(self::GLOBAL_POLICY_LINES, [self::WRONG_PREFIX_LINE]),
        ];
    }

    public static function payloadWithIntegratorDocument(): array
    {
        return [
            'policy_lines' => self::GLOBAL_POLICY_LINES,
            'integrator_document' => [
                'tenant' => 'acme',
                'features' => ['reports'],
            ],
        ];
    }
}
