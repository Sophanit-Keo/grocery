<?php

test('the application returns a successful response', function () {
    $this->getJson('/')
        ->assertOk()
        ->assertExactJson([
            'name' => config('app.name'),
            'status' => 'ok',
            'api_version' => 'v1',
        ]);
});
