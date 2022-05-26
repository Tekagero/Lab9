<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageTest extends WebTestCase
{
    public function testHomePage(): void
    {

        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleContains('Новостной сайт Киренского.');
        $this->assertCount(5, $crawler->filter('.carousel-item'));
        $link = $crawler->selectLink('Главная страница')->link();
        $client->click($link);
        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleContains('Новостной сайт Киренского.');
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $link = $crawler->selectLink('Login')->link();
        $client->click($link);
        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleContains('Log in!');
        $falseUserData = ['_username' => 'Неправильный пользователь',
            '_password' => 'и пароль неправильный'];
        $client->submitForm('Авторизоваться', $falseUserData);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Invalid credentials.');
        $trueUserData = ['_username' => 'User1',
            '_password' => 'password1'];
        $client->submitForm('Авторизоваться', $trueUserData);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertPageTitleContains('Новостной сайт Киренского.');
    }

    public function testAddNews(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/add-news/');
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertPageTitleContains('Log in!');
        $client->submitForm('Авторизоваться', ['_username' => 'User1', '_password' => 'password1']);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $crawler = $client->request('GET', '/add-news/');
        $this->assertResponseRedirects();
        $client->followRedirect();
        var_dump($client->getResponse()->getContent());
        $news = [
            'news[name]' => 'Название новости',
            'news[annotation]' => 'Аннотация новости',
            'news[text]' => 'Текст новости, он слишком маленький'
        ];
        $client->submitForm('Отправить', $news);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertPageTitleContains('Добавление новости');
        $news = [
            'news[name]' => 'Название новости',
            'news[annotation]' => 'Аннотация новости',
            'news[text]' => 'Текст новости, но не слишком маленький
            Вообще не маленький
            Нужно 50 символов минимум
            Это очень-очень-очень-очень-очень много
            Еще больше-больше-больше-больше
            Достаточно.'
        ];
        $client->submitForm('Отправить', $news);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertPageTitleContains('Название новости');
    }

}
