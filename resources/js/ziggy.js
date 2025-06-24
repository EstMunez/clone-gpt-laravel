const Ziggy = {"url":"http:\/\/localhost","port":null,"defaults":{},"routes":{"ask.index":{"uri":"ask","methods":["GET","HEAD"]},"ask.send":{"uri":"ask\/send","methods":["POST"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
