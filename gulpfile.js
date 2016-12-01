var gulp       = require('gulp');
var elixir     = require('laravel-elixir');
var reactify   = require('reactify');

elixir((mix) => {
    mix.webpack('component.jsx');
});
