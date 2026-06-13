<?php

namespace Tests\Amtgard\IAM\Allowance;

use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\Allowance\PolicyBuilder;
use Amtgard\IAM\Allowance\PolicyDocument;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\PolicyFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Amtgard\IAM\Fixtures\IdpJwtPolicyFixture;

class PolicyDocumentTest extends TestCase
{
    private OrkRequirement $orkRequirement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orkRequirement = new OrkRequirement(
            ServiceCatalog::ORK,
            'ORK:1:7:8:9:10:ORK/AddKingdom'
        );
    }

    public function testFromOrnListBuildsGlobalOnlyPolicy(): void
    {
        $doc = PolicyDocument::fromOrnList(IdpJwtPolicyFixture::GLOBAL_POLICY_LINES);

        self::assertInstanceOf(Policy::class, $doc->policy());
        self::assertTrue($doc->policy()->isAuthorized($this->orkRequirement));
        self::assertNull($doc->integratorDocument());
    }

    public function testFromOrnListWithoutIntegratorDocument(): void
    {
        $doc = PolicyDocument::fromOrnList(['ORK:1:::::*']);

        self::assertNull($doc->integratorDocument());
        self::assertTrue($doc->policy()->isAuthorized($this->orkRequirement));
    }

    public function testFromVerifiedPayloadIncludesGlobalAndIntegratorLines(): void
    {
        $doc = PolicyDocument::fromVerifiedPayload(IdpJwtPolicyFixture::globalAndIntegratorPayload());

        self::assertTrue($doc->policy()->isAuthorized($this->orkRequirement));
        self::assertTrue($doc->policy()->isAuthorized(
            new OrkRequirement(ServiceCatalog::ORK, 'ORK:7:3:8:9:10:ORK/AddKingdom')
        ));
    }

    public function testWrongPrefixIntegratorLineDoesNotBlockGlobalAuthorization(): void
    {
        $doc = PolicyDocument::fromVerifiedPayload(
            IdpJwtPolicyFixture::globalWithWrongPrefixIntegratorLine()
        );

        self::assertTrue($doc->policy()->isAuthorized($this->orkRequirement));
    }

    public function testFromJsonRoundTripsOrnArray(): void
    {
        $policy = PolicyFactory::fromOrn(IdpJwtPolicyFixture::GLOBAL_POLICY_LINES);
        $doc = PolicyDocument::fromJson($policy->toJson());

        self::assertTrue($doc->policy()->is($policy));
    }

    public function testFromVerifiedJwtAliasMatchesFromVerifiedPayload(): void
    {
        $payload = IdpJwtPolicyFixture::globalAndIntegratorPayload();

        self::assertTrue(
            PolicyDocument::fromVerifiedJwt($payload)
                ->policy()
                ->is(PolicyDocument::fromVerifiedPayload($payload)->policy())
        );
    }

    public function testMergeEqualsFromOrnListForCombinedLines(): void
    {
        $global = PolicyFactory::fromOrn(IdpJwtPolicyFixture::GLOBAL_POLICY_LINES);
        $integrator = PolicyFactory::fromOrn(IdpJwtPolicyFixture::INTEGRATOR_POLICY_LINES);

        $merged = PolicyBuilder::from($global)
            ->merge($integrator)
            ->build();

        $fromList = PolicyDocument::fromOrnList(
            array_merge(IdpJwtPolicyFixture::GLOBAL_POLICY_LINES, IdpJwtPolicyFixture::INTEGRATOR_POLICY_LINES)
        )->policy();

        self::assertTrue($merged->is($fromList));
    }

    public function testIntegratorDocumentExposedButDoesNotAffectAuthorization(): void
    {
        $doc = PolicyDocument::fromVerifiedPayload(IdpJwtPolicyFixture::payloadWithIntegratorDocument());

        self::assertSame(
            ['tenant' => 'acme', 'features' => ['reports']],
            $doc->integratorDocument()
        );
        self::assertTrue($doc->policy()->isAuthorized($this->orkRequirement));
    }

    public function testWhenPolicyLinesMissing_thenFromVerifiedPayloadThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('policy_lines');

        PolicyDocument::fromVerifiedPayload([]);
    }

    public function testWhenIntegratorDocumentIsNotArray_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('integrator_document');

        PolicyDocument::fromVerifiedPayload([
            'policy_lines' => IdpJwtPolicyFixture::GLOBAL_POLICY_LINES,
            'integrator_document' => 'not-an-array',
        ]);
    }

    public function testWhenJsonIsInvalid_thenFromJsonThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PolicyDocument::fromJson('{"not":"an-array"}');
    }
}
