var adminDirectives = angular.module('adminDirectives',[]);
adminDirectives.directive('restrictRules', ['$authService', '$animate', function($authService, $animate){
    return{
        restrict: 'A',
        scope: {
            restrictRules:'='
        },
        multiElement: true,
        transclude: 'element',
        priority: 600,
        terminal: true,
        $$tlb: true,
        link: function($scope, $element , $attr, ctrl, $transclude){
            $scope.user = $authService.getUser();
            var block, childScope, previousElements;
            function getBlockNodes(nodes) {
                var node = nodes[0];
                var endNode = nodes[nodes.length - 1];
                var blockNodes;

                for (var i = 1; node !== endNode && (node = node.nextSibling); i++) {
                    if (blockNodes || nodes[i] !== node) {
                        if (!blockNodes) {
                            blockNodes = jqLite(slice.call(nodes, 0, i));
                        }
                        blockNodes.push(node);
                    }
                }
                return blockNodes || nodes;
            }
            $scope.$watch('user', function(user){
                for(var i=0; i< $scope.restrictRules.length; i++){
                    if($scope.restrictRules[i] == 'all' || user.role.toLowerCase() == $scope.restrictRules[i]){
                        if (!childScope) {
                            $transclude(function(clone, newScope) {
                                childScope = newScope;
                                block = {
                                    clone: clone
                                };
                                $animate.enter(clone, $element.parent(), $element);
                            });
                        }
                        break;
                    } else {
                        if (previousElements) {
                            previousElements.remove();
                            previousElements = null;
                        }
                        if (childScope) {
                            childScope.$destroy();
                            childScope = null;
                        }
                        if (block) {
                            previousElements = getBlockNodes(block.clone);
                            $animate.leave(previousElements).then(function() {
                                previousElements = null;
                            });
                            block = null;
                        }
                    }
                }
            }, true);

        }
    }
}]);

