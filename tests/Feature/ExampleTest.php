<?php

test('a guest visiting the root is redirected to the ideas index, then to login', function () {
    $response = $this->get('/');
    $response->assertRedirect('/ideas');

    $response = $this->get('/ideas');
    $response->assertRedirect(route('login'));
});
