<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Unit;

use CornellCustomDev\LaravelStarterKit\Ldap\LdapData;
use CornellCustomDev\LaravelStarterKit\Tests\TestCase;

class LdapDataTest extends TestCase
{
    public function testResponseIsParsedToLdapData()
    {
        $ldapResponse = $this->fixture('ldap_search.json', json: true);

        $ldapData = LdapData::parseResponse($ldapResponse);
        $result = LdapData::make($ldapData);

        $this->assertEquals('tt999', $result->netid);
        $this->assertEquals('9999999', $result->emplid);
        $this->assertEquals('Testy', $result->firstName);
        $this->assertEquals('Testerson', $result->lastName);
        $this->assertEquals('Testy Testerson', $result->displayName);
        $this->assertEquals('testerson@cornell.edu', $result->email);
        $this->assertEquals('607/2551111', $result->campusPhone);
        $this->assertEquals('CIO - CIT Enterprise Services', $result->deptName);
        $this->assertEquals('Web Developer', $result->workingTitle);
        $this->assertEquals('staff', $result->primaryAffiliation);
        $this->assertEquals(['staff'], $result->affiliations);
        $this->assertEquals(null, $result->previousNetids);
        $this->assertEquals(null, $result->previousEmplids);
    }

    public function testFerpaShowsCornellStudent()
    {
        $ldapResponse = $this->fixture('ldap_ferpa.json', json: true);

        $ldapData = LdapData::parseResponse($ldapResponse);
        $result = LdapData::make($ldapData);

        $this->assertEquals('tt999', $result->netid);
        $this->assertEquals('9999999', $result->emplid);
        $this->assertEquals('Cornell', $result->firstName);
        $this->assertEquals('Student', $result->lastName);
        $this->assertEquals('Cornell Student', $result->displayName);
        $this->assertEquals('tt999@cornell.edu', $result->email);
        $this->assertEquals(null, $result->campusPhone);
        $this->assertEquals(null, $result->deptName);
        $this->assertEquals(null, $result->workingTitle);
        $this->assertEquals('student', $result->primaryAffiliation);
        $this->assertEquals(['alumni', 'student'], $result->affiliations);
    }
}
