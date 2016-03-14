<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

  <title>{{{ $path->getUrl() }}} - Madokami</title>
  <link rel="self" href="{{{URL::to('/')}}}{{{ $path->getUrl() }}}?t=atom" />
  <id>{{{URL::to('/')}}}{{{ $path->getUrl() }}}</id>
  <updated>{{{ (new DateTime(date('Y-m-d H:i:s', $updated)))->format(DateTime::ISO8601)}}}</updated>

  @foreach($children as $child)@if(!$child->isDir)<entry>
    <title>{{{ $child->name }}}</title>
    <link href="{{{URL::to('/')}}}{{{ $child->url }}}" />
                            <link href="{{{URL::to('/')}}}{{{ $child->url }}}" rel="enclosure" type="{{{$child->mime}}}" length="{{{$child->rawSize}}}" />
    <id>{{{URL::to('/')}}}{{{ $child->url }}}?length={{{ $child->rawSize }}}&amp;mtime={{{ $child->rawTime}}}</id>
    <updated>{{{ (new DateTime(date('Y-m-d H:i:s', $child->rawTime)))->format(DateTime::ISO8601)}}}</updated>
  </entry>
 @endif @endforeach

</feed>

