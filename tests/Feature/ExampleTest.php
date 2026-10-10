<?php

it('redirects guests to login from the root page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
